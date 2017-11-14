<?php

namespace App\Console\Commands;

use App\Exceptions\AppException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Zdp\Main\Data\Models\DpBrands;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Models\Markup;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;

class MigrateOldMarkUp extends Command
{
    /**
     * @var ProgressBar
     */
    protected $bar;

    protected $brands = [];

    protected $markets = [];

    protected $errors = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'markup:migrate:v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将版本一的加价数据迁移至版本二';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->bar = $this->output->createProgressBar();
        $this->bar->setMessage("初始化");

        $total = Markup::query()
                       ->where('type', 1)
                       ->count();
        $this->bar->start($total);

        Markup::query()
              ->where('type', 1)
              ->chunk(100, [$this, 'migrate']);
    }

    /**
     * @param Markup[]|\Illuminate\Database\Eloquent\Collection $markups
     */
    public function migrate($markups)
    {
        foreach ($markups as $markup) {
            try {
                $this->migrateSingle($markup);
            } catch (\Exception $e) {
                $this->warn($e->getMessage());
            }
        }
    }

    /**
     * @param Markup $markup
     */
    protected function migrateSingle($markup)
    {
        $marketIds = $this->retriveMarkets($markup->sp_id);

        if (empty($marketIds)) {
            throw new AppException("服务商 #{$markup->sp_id} 没有配置对应市场");
        }

        $brandIds = $this->retriveBrand($markup->sort_id, $marketIds);
        foreach ($brandIds as $brandId) {
            $single = GoodsCategoryBrand
                ::query()
                ->firstOrNew(
                    [
                        'sp_id'    => $markup->sp_id,
                        'sort_id'  => $markup->sort_id,
                        'brand_id' => $brandId,
                    ],
                    [
                        'display' => GoodsCategoryBrand::DISPLAY_NORMAL,
                    ]
                );
            $single->increase = $markup->increase - 1;
            $single->save();
        }

        $this->bar->advance();
    }

    /**
     * 获取品牌列表
     *
     * @param int $sortId 获取某个分类下的品牌列表
     */
    protected function retriveBrand($sortId, $marketIds)
    {
        $key = $sortId . '.' . implode(',', $marketIds);

        if (key_exists($key, $this->brands)) {
            return $this->brands[$key];
        }

        // 获取子分类对应的品牌
        $brands = DpBrands
            ::query()
            ->join('dp_goods_info', function ($query) use ($sortId) {
                $query->on('dp_goods_info.brand_id', '=', 'dp_brands.id')
                      ->where(
                          'dp_goods_info.shenghe_act',
                          '=',
                          DpGoodsInfo::STATUS_NORMAL
                      )
                      ->where('dp_goods_info.goods_type_id', $sortId);
            })
            ->join('dp_shopinfo', function ($query) use ($marketIds) {
                $query->on('dp_shopinfo.shopid', '=', 'dp_goods_info.shopid')
                      ->whereIn('dp_shopinfo.pianquid', $marketIds);
            })
            ->pluck('dp_brands.id')
            ->all();

        $this->brands[$key] = $brands;

        return $brands;
    }

    /**
     * 解析服务商进货市场
     *
     * @param int $spId
     *
     * @return array
     */
    protected function retriveMarkets($spId)
    {
        if (key_exists($spId, $this->markets)) {
            return $this->markets[$spId];
        }

        $marketIds = ServiceProvider::query()
                                    ->where('zdp_user_id', $spId)
                                    ->value('market_ids');

        if (empty($marketIds)) {
            $markets = [];
        } else {
            $markets = explode(',', $marketIds);
        }

        $this->markets[$spId] = $markets;

        return $markets;
    }
}
