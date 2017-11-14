<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-3
 * Time: 下午3:01
 */

namespace App\Http\Controllers\Shop;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Services\GoodsCategoryBrand;

class ShopController extends Controller
{
    /**
     * 获取店铺信息
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function getInfo()
    {
        $serviceProvider = getServiceProvider();
        if (empty($serviceProvider)) {
            throw new AppException("你还没有完善信息");
        }

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => [
                    'shop_name' => $serviceProvider->shop_name,
                    'address'   => $serviceProvider->address,
                    'mobile'    => $serviceProvider->mobile,
                ],
            ]
        );
    }

    /**
     * 更新市场
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMarkets(Request $request)
    {
        $this->validate(
            $request,
            [
                'market_ids'   => 'array',
                'market_ids.*' => 'required_with:market_ids|integer',
            ],
            [
                'market_ids.array'     => '市场id数组不能为空',
                'market_ids.*.integer' => '市场id数组值应该都是整形',
            ]
        );

        if ($request->has('market_ids')) {
            $serviceProvider = getServiceProvider();
            $serviceProvider->update(
                [
                    'market_ids' => implode(",", $request->input('market_ids')),
                ]
            );
        }

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => [],
            ]
        );
    }

    /**
     * 获取分类价格管理列表
     *
     * @param Request     $request
     * @param ShopService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSortPrices(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id' => 'required|exists:main_mysql.dp_goods_types,id',
            ],
            [
                'sort_id.required' => '分类必须有',
                'sort_id.exists'   => '分类不存在',
            ]
        );

        $sorts = $service->getMarkUpCategoryBrandList(
            $request->input('sort_id')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sorts,
        ]);
    }

    /**
     * 获取分类列表
     *
     * @param Request     $request
     * @param ShopService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSorts(Request $request, ShopService $service)
    {
        $this->validate(
            $request,
            [
                //                'market_id' => 'required|exists:main_mysql.dp_pianqu,pianquId',
                'status' => 'required|in:0,1,2',
            ],
            [
                //                'market_id.required' => '市场必须有',
                //                'market_id.exists'   => '市场不存在',

                'status.required' => '加价状态必须有',
                'status.in'       => '加价状态应该是0,1,2',
            ]
        );

        $sorts = $service->getSorts(
            $request->input('market_id'),
            $request->input('status')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sorts,
        ]);
    }

    /**
     * 获取可选市场列表
     *
     * @param ShopService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMarketList(ShopService $service)
    {
        $serviceProvider = getServiceProvider();
        $listDataArr = $service->getMarketList($serviceProvider);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listDataArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 更新付款方式
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePayMethods(Request $request)
    {
        $payMethodsArr = Order::$paymentArr;
        $payMethods = implode(',', $payMethodsArr);
        $this->validate(
            $request,
            [
                'pay_methods'   => 'required|array',
                'pay_methods.*' => "in:{$payMethods}",
            ],
            [
                'pay_methods.required' => '付款方式不能为空',
                'pay_methods.array'    => '付款方式是个数组',
                'pay_methods.*.in'     => "付款方式只能在:{$payMethods}",
            ]
        );
        $weChatAccounts = getWeChatAccount();
        $weChatAccounts->pay_methods =
            implode(',', $request->input('pay_methods'));
        $weChatAccounts->save();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }
}
