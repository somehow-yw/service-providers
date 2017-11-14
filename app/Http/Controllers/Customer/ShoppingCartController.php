<?php

namespace App\Http\Controllers\Customer;

use App\Services\ShoppingCartService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShoppingCartController extends Controller
{
    /**
     * 获取购物车列表商品
     *
     * @param ShoppingCartService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShoppingCart(ShoppingCartService $service)
    {
        $user = getUser();
        $shoppingCart = $service->getShoppingCart($user->id);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $shoppingCart,
        ]);
    }

    /**
     * 添加商品到购物车
     *
     * @param Request             $request
     * @param ShoppingCartService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addGoodsToShoppingCart(Request $request, ShoppingCartService $service)
    {
        $this->validate(
            $request,
            [
                'goods_id' => 'required|exists:main_mysql.dp_goods_info,id',
                'buy_num'  => 'required|integer|min:1|max:65535',
            ],
            [
                'goods_id.required' => '商品id必须有',
                'goods_id.exists'   => '商品id不存在',

                'buy_num.required' => '购买数量不能为空',
                'buy_num.integer'  => '购买数量应该是个整数',
                'buy_num.min'      => '购买数量应该大于0',
                'buy_num.max'      => '购买数量应该小于65535',
            ]
        );

        $service->addGoodsToShoppingCart($request->input('goods_id'), $request->input('buy_num'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 动态计算购物车商品价格和数量
     *
     * @param Request             $request
     * @param ShoppingCartService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcShoppingCart(Request $request, ShoppingCartService $service)
    {
        $this->validate(
            $request,
            [
                'goods'            => 'array',
                'goods.*.goods_id' => 'required|exists:main_mysql.dp_goods_info,id',
                'goods.*.buy_num'  => 'required|integer|min:1|max:65535',
            ],
            [
                'goods.*.goods_id.required' => '商品id必须有',
                'goods.*.goods_id.exists'   => '商品id不存在',

                'goods.*.buy_num.required' => '购买数量不能为空',
                'goods.*.buy_num.integer'  => '购买数量应该是个整数',
                'goods.*.buy_num.min'      => '购买数量应该大于0',
                'goods.*.buy_num.max'      => '购买数量应该小于655350',
            ]
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->calcShoppingCart($request->input('goods')),
        ]);
    }

    /**
     * 更新购物车
     *
     * @param Request             $request
     *
     * @param ShoppingCartService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShoppingCart(Request $request, ShoppingCartService $service)
    {
        $this->validate(
            $request,
            [
                'goods'            => 'array',
                'goods.*.goods_id' => 'required|exists:main_mysql.dp_goods_info,id',
                'goods.*.buy_num'  => 'required|integer|min:1|max:65535',
            ],
            [
                'goods.*.goods_id.required' => '商品id必须有',
                'goods.*.goods_id.exists'   => '商品id不存在',

                'goods.*.buy_num.required' => '购买数量不能为空',
                'goods.*.buy_num.integer'  => '购买数量应该是个整数',
                'goods.*.buy_num.min'      => '购买数量应该大于0',
                'goods.*.buy_num.max'      => '购买数量应该小于65535',
            ]
        );

        $service->updateShoppingCart($request->input('goods'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }

    /**
     * 删除购物车商品
     *
     * @param Request             $request
     *
     * @param ShoppingCartService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request, ShoppingCartService $service)
    {
        $this->validate(
            $request,
            [
                'goods_ids' => 'required|array',
            ],
            [
                'goods_ids.required' => '商品id必须有',
                'goods_ids.array'    => '商品id为数组',
            ]
        );

        $user = getUser();

        $service->del(
            $user->id,
            $request->input('goods_ids')
        );

        return response()->json([
            'code'    => 0,
            'message' => '删除成功',
            'data'    => [],
        ]);
    }
}
