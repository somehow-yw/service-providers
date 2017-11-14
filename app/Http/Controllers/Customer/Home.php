<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Services\HomeItem;
use Zdp\ServiceProvider\Data\Models\HomeItem as HomeItemModel;

/**
 * Class CustomController.
 * 会员信息操作
 *
 * @package App\Http\Controllers\Custom
 */
class Home extends Controller
{
    public function index(Request $request, HomeItem $service)
    {
        $sp = getServiceProvider();

        if ($sp->enable_custom_homepage == 0) {
            return response()->json([
                'code'    => 0,
                'message' => 'OK',
                'data'    => [
                    'enable' => 0,
                ],
            ]);
        }

        if ($request->input('with_out_info')) {
            return response()->json([
                'code'    => 0,
                'message' => 'OK',
                'data'    => [
                    'enable' => 1,
                ],
            ]);
        }

        $cate = $service
            ->getHomeItemByType(HomeItemModel::TYPE_HOT_GOODS_CATEGORY);
        $brand = $service
            ->getHomeItemByType(HomeItemModel::TYPE_HOT_GOODS_BRAND);
        $goods = $service->getHotGoodsForCustomer();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'enabled'        => 1,
                'hot_categories' => $service->formatForHome($cate),
                'hot_brands'     => $service->formatForHome($brand),
                'hot_goods'      => $service->formatForHome($goods,
                    HomeItemModel::TYPE_HOT_GOODS),
            ],
        ]);
    }
}
