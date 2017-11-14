<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Zdp\ServiceProvider\Data\Models\Order;
use App\Workflows\SaleWorkflow;

class SaleController extends Controller
{
    private $service;
    private $user;

    public function __construct(SaleService $saleService)
    {
        $this->service = $saleService;
        $this->user = getServiceProvider();
    }

    /**
     * 获取所有的销售单信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->validate(
            $request,
            [
                'status' => 'integer', Rule::in(Order::$statusArr),
                'page'   => 'integer|min:1',
                'size'   => 'integer|min:10|max:50',
            ],
            [
                'status.integer' => '状态为int',
                'status.in'      => '查询状态不存在',
            ]
        );

        $orders = $this->service->index(
            $this->user->zdp_user_id,
            $request->input('status'),
            $request->input('page', 1),
            $request->input('size', 20)
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $orders,
        ]);
    }

    /**
     * 订单状态改变接口(确认、取消、发货、收货)
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @internal param $id
     *
     */
    public function update(Request $request)
    {
        $this->validate(
            $request,
            [
                'order_ids' => 'required|array',
                'handle'    => 'required|in:0,1,2,3',
            ],
            [
                'order_ids.required' => '订单id必须有',
                'order_ids.array'    => '订单id为数组',

                'handle.required' => '操作类型必须有',
                'handle.in'       => '操作类型不存在',
            ]
        );
        $this->service->handle(
            $request->input('order_ids'),
            $this->user->zdp_user_id,
            $request->input('handle')
        );

        return response()->json([
            'code'    => 0,
            'message' => '操作成功',
            'data'    => [],
        ]);
    }

    /**
     * 服务商进货处理
     *
     * @param \Illuminate\Http\Request $request
     * @param SaleWorkflow             $workflow
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function goodsPurchase(Request $request, SaleWorkflow $workflow)
    {
        $this->validate(
            $request,
            [
                'order_id'     => 'required|integer|min:1|exists:orders,id',
                'goods_id'     => 'required|integer|min:1|exists:main_mysql.dp_goods_info,id',
                'purchase_num' => 'required|integer|between:1,32767',
            ],
            [
                'order_id.required' => '订单ID必须有',
                'order_id.integer'  => '订单ID必须是一个整型',
                'order_id.min'      => '订单ID不可小于:min',
                'order_id.exists'   => '订单不存在',

                'goods_id.required' => '商品ID必须有',
                'goods_id.integer'  => '商品ID必须是一个整型',
                'goods_id.min'      => '商品ID不可小于:min',
                'goods_id.exists'   => '商品不存在',

                'purchase_num.required' => '采购数量必须有',
                'purchase_num.integer'  => '采购数量必须是一个整型',
                'purchase_num.in'       => '采购数量必须是:min到:max的整数',
            ]
        );

        $spInfo = getServiceProvider();
        $workflow->goodsPurchase(
            $request->input('order_id'),
            $request->input('goods_id'),
            $request->input('purchase_num'),
            $spInfo->zdp_user_id
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ]);
    }
}
