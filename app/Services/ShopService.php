<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 18:02
 */

namespace App\Services;

use App\Exceptions\Shop\ShopException;
use App\Repositories\Shop\Contracts\MainGoodsRepository;
use App\Repositories\Shop\Contracts\MainUserRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Zdp\Main\Data\Models\DpMarketInfo;
use Zdp\Main\Data\Models\DpSortAvgPriceLog;
use Zdp\ServiceProvider\Data\Models\Markup;

/**
 * 服务商信息处理
 * Class ShopService
 *
 * @package App\Services
 */
class ShopService
{
    private $mainUserRepo;
    private $mainGoodsRepo;

    public function __construct(
        MainUserRepository $mainUserRepo,
        MainGoodsRepository $mainGoodsRepo
    ) {
        $this->mainUserRepo = $mainUserRepo;
        $this->mainGoodsRepo = $mainGoodsRepo;
    }

    /**
     * 获取可选市场列表
     *
     * @param $serviceProvider \Zdp\ServiceProvider\Data\Models\ServiceProvider
     *                         服务商
     *
     * @throws ShopException
     */
    public function getMarketList($serviceProvider)
    {
        $zdpUserId = $serviceProvider->zdp_user_id;
        // 获得此会员所在省的ID
        $selectArr = [
            'user' => ['shId', 'shopId'],
            'shop' => [
                'shopId',
                'pianquId',
                'province_id',
                'city_id',
                'county_id',
            ],
        ];
        $zdpUserInfo =
            $this->mainUserRepo->getUserAndShopInfoByUserId($zdpUserId,
                $selectArr);
        if (is_null($zdpUserInfo)) {
            throw new ShopException(ShopException::MAIN_INFO_DEL);
        }

        // 获得此省下面的所有上游市场
        /** @var Collection $marketInfo */
        $marketInfo =
            DpMarketInfo::getShopMarket($zdpUserInfo->shop->province_id)
                        ->select([
                            'pianquId as market_id',
                            'pianqu as market_name',
                        ])
                        ->get();

        // 获取服务商当前市场
        $currentMarketIds =
            array_map('intval', explode(",", $serviceProvider->market_ids));
        $marketInfo = $marketInfo->toArray();

        foreach ($marketInfo as $key => $value) {
            if (in_array($value['market_id'], $currentMarketIds)) {
                $marketInfo[$key]['selected'] = true;
            } else {
                $marketInfo[$key]['selected'] = false;
            }
        }

        return $marketInfo;
    }

    /**
     * 获取分类加价列表
     *
     * @param $sortId
     * @param $status
     *
     * @return array
     */
    public function getSortPrices($sortId, $status)
    {
        $sorts = $this->mainGoodsRepo->getGoodsSortInfo($sortId);
        $this->sortMarkUpHandler($sorts, $status);

        return $sorts->values()->toArray();
    }

    /**
     * 获取分类列表
     *
     * @param     $marketId integer 市场id
     * @param int $status   状态 全部 0 已经加价 1 未加价 2
     *
     * @return array
     */
    public function getSorts($marketId, $status = 0)
    {
        return $this->mainGoodsRepo->getGoodsSorts($marketId, $status);
    }

    /**
     * 处理分类价格
     *
     * @param     $sorts  Collection
     * @param int $status 状态 0 全部 1 加价 2 未加价
     */
    protected function sortMarkUpHandler(&$sorts, $status = 0)
    {
        $sortIds = $sorts->pluck('id')->toArray();
        if (count($sortIds)) {
            $serviceProvider = getServiceProvider();
            $spId = $serviceProvider->zdp_user_id;

            $markUpMapArr = Markup::getMarkUpMapArray($spId, $sortIds);

            //Fetch yesterday sort sell number
            $yesterday = date('Y-m-d', strtotime("-1 days"));

            $sortAvgPriceMapArray =
                DpSortAvgPriceLog::getAvgPriceMapArray($yesterday, $sortIds);

            /** @var Collection $_ */
            foreach ($sorts as $_) {
                $sortId = $_['id'];
                $isMarkUp = array_key_exists($sortId, $markUpMapArr);
                $_['is_mark_up'] = $isMarkUp;

                if ($isMarkUp) {
                    $_['increase'] = (float)$markUpMapArr[$sortId]['increase'];
                    $_['type'] = $markUpMapArr[$sortId]['type'];
                } else {
                    $_['increase'] = 0.0;
                    $_['type'] = Markup::MARK_UP_TYPE_PER;
                }

                if (array_key_exists($sortId, $sortAvgPriceMapArray)) {
                    $_['avg_price'] = (float)$sortAvgPriceMapArray[$sortId];
                } else {
                    $_['avg_price'] = 0.0;
                }

                $_['total_sales'] = (int)array_get($_, 'total_sales', 0);
            }

            if ($status) {
                $parseStatus = function ($status) {
                    if ($status == Markup::MARK_UP) {
                        return true;
                    } elseif ($status == Markup::NOT_MARK_UP) {
                        return false;
                    }

                    return $status;
                };
                $sorts = $sorts->where('is_mark_up', $parseStatus($status));
            }
        }
    }
}
