<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-2
 * Time: 上午10:33
 */

namespace App\Http\Controllers\Other\Common;

use DB;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Models\ShopType;
use App\Services\AreaService;
use App;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Utils\UserTag;
use App\Models\Area;

class CommonController extends Controller
{
    /**
     * 获取当前服务商的基本信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpInfo()
    {
        $serviceProvider = getServiceProvider();
        $provinceName = Area::getName($serviceProvider->province_id);
        $cityName = Area::getName($serviceProvider->city_id);
        $countyName = Area::getName($serviceProvider->county_id);
        $address =
            $provinceName . $cityName . $countyName . $serviceProvider->address;
        $reDataArr = [
            'address'   => $address,
            'shop_name' => $serviceProvider->shop_name,
            'user_name' => $serviceProvider->user_name,
            'mobile'    => $serviceProvider->mobile,
        ];

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => $reDataArr,
        ]);
    }

    /**
     * 激活卖家菜单
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function activate()
    {
        $serviceProvider = getServiceProvider();
        if (is_null($serviceProvider)) {
            throw new AppException("请先申请成为服务商");
        }
        if (!empty($serviceProvider->wechat_openid)) {
            throw new AppException("你已经成功激活此服务商账户");
        }

        DB::transaction(function () use ($serviceProvider) {
            $wechatUser = getWeChatOAuthUser();
            $openId = $wechatUser->getId();
            $serviceProvider->wechat_openid = $openId;
            $serviceProvider->save();
            $userTag = new UserTag(config('wechat'));
            $userTag->tagShop($openId);
        });

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => [],
        ]);
    }

    /**
     * 获取中国所有省
     *
     * @param \App\Services\AreaService $area
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvinces(AreaService $area)
    {
        $provinces = $area->getProvince();

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $provinces,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取某区域下的所有子区域
     *
     * @param $id integer
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getChildren($id)
    {
        if (empty($id)) {
            throw new \Exception('请传入需要查询的区域id');
        }

        /** @var AreaService $area */
        $area = App::make(AreaService::class);
        $cities = $area->getChildren($id);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $cities,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取店铺分类列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopCategories()
    {
        $categories = ShopType::select('id', 'type_name')
                              ->orderBy('sort_value')->get()->toArray();

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => $categories,
        ]);
    }

    /**
     * 获取所有可支付方式
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayments()
    {
        $wechatAccount = getWeChatAccount();
        $shopPayMethodIds = explode(',', $wechatAccount->pay_methods);
        $payMethods = Order::$paymentNameArr;
        $returnPayMethods = [];
        foreach ($payMethods as $payMethodId => $payMethodName) {
            if (in_array($payMethodId, $shopPayMethodIds)) {
                $selected = true;
            } else {
                $selected = false;
            }
            array_push($returnPayMethods, [
                'id'       => $payMethodId,
                'pay_name' => $payMethodName,
                'selected' => $selected,
            ]);
        }

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => $returnPayMethods,
        ]);
    }

    /**
     * 获取所有可配送方式
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeliveryList()
    {
        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => Order::$deliveryNameArr,
        ]);
    }

    /**
     * 获取该服务商的地址信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopLocation()
    {
        $shop = getServiceProvider();

        $areas = $shop->areas;

        $areasArr = [];

        foreach ($areas as $area) {
            array_push(
                $areasArr,
                $area->asShortArray()
            );
        }

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => $areasArr,
        ]);
    }
}
