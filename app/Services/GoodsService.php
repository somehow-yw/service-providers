<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-8
 * Time: 上午11:16
 */

namespace App\Services;

use App\Exceptions\AppException;
use App\Repositories\Shop\Contracts\MainGoodsRepository;
use Illuminate\Support\Collection;
use Zdp\Main\Data\Models\DpBrands;
use Zdp\Main\Data\Models\DpGoodsBasicAttribute;
use Zdp\Main\Data\Models\DpGoodsSpecialAttribute;
use Zdp\Main\Data\Models\DpGoodsType;
use Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Models\Markup;

class GoodsService
{
    protected $mainGoodsRepo;

    public function __construct(MainGoodsRepository $mainGoodsRepo)
    {
        $this->mainGoodsRepo = $mainGoodsRepo;
    }

    public function getBrands($search = null, $page = 1, $size = 20)
    {
        $query = DpBrands::query();

        if (!empty($search)) {
            $query
                ->where('brand', 'like', "%{$search}%")
                ->orWhere('key_words', 'like', "%{$search}%");
        }

        $pager = $query->paginate($size, ['brand', 'id'], null, $page);

        return [
            'brands'    => $pager->items(),
            'total'     => $pager->total(),
            'last_page' => $pager->lastPage(),
            'current'   => $pager->currentPage(),
        ];
    }

    /**
     * 获取商品详情
     *
     * @param      $goodsId
     * @param bool $withOriginPrice
     *
     * @return array
     */
    public function getGoodsInfo($goodsId, $withOriginPrice = false)
    {
        /** @var Collection $goodsInfo */
        $goodsInfo = $this->mainGoodsRepo->getGoodsInfo($goodsId);
        if (empty($goodsInfo)) {
            return [];
        }

        $goodsInfoArr = $goodsInfo->toArray();

        if ($withOriginPrice) {
            $goodsInfoArr['origin_goods_price'] = $goodsInfo->goods_price;
        }

        $typeId = $goodsInfo->goods_type_id;
        $brandId = $goodsInfo->goods_brand_id;

        $serviceProvider = getServiceProvider();

        $increase = GoodsCategoryBrand
            ::query()
            ->where('sp_id', $serviceProvider->zdp_user_id)
            ->where('sort_id', $typeId)
            ->where('brand_id', $brandId)
            ->first();

        if (!empty($increase)) {
            if ($increase->display == GoodsCategoryBrand::DISPLAY_BLACKLIST) {
                return [];
            }

            $goodsInfoArr['goods_price'] = GoodsCategoryBrand::markUpPrice(
                $typeId,
                $brandId,
                $goodsInfo->goods_price,
                ["{$typeId}.{$brandId}" => $increase->increase],
                $goodsInfo->goods_unit
            );
        } else {
            $goodsInfoArr['goods_price'] = GoodsCategoryBrand::markUpPrice(
                $typeId,
                $brandId,
                $goodsInfo->goods_price,
                [],
                $goodsInfo->goods_unit
            );
        }

        $goodsInfoArr['expired'] =
            $goodsInfo->auto_soldout_time <= date('Y-m-d H:i:s');

        $goodsInfoArr['goods_unit'] =
            DpGoodsBasicAttribute::getGoodsUnitName($goodsInfo->goods_unit);

        $goodsInfoArr['special_detail'] =
            DpGoodsSpecialAttribute::where('goodsid', $goodsId)
                                   ->select('prope_name', 'prope_value')
                                   ->get();

        return $goodsInfoArr;
    }

    /**
     * 获取分类列表
     *
     * @param     $marketId integer 市场id
     * @param int $status   状态 全部 0 已经加价 1 未加价 2
     *
     * @return array
     */
    public function getSorts($marketId = 0, $status = 0, $series = 3)
    {
        return $this->mainGoodsRepo->getGoodsSorts($marketId, $status, $series);
    }
}