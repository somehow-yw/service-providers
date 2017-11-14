<?php
/**
 * Created by PhpStorm.
 * <<<<<<< HEAD
 * User: coderxiao
 * Date: 17-3-9
 * Time: 下午3:29
 * =======
 * User: Administrator
 * Date: 2017/3/9
 * Time: 14:43
 * >>>>>>> 10f33a084d868da6b79b6d33b8d7b44f0c3b6179
 */

namespace App\Http\Controllers\Customer;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Workflows\PaymentQueryWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\OrderLog;

/**
 * 客户订单管理
 * Class OrderController
 *
 * @package App\Http\Controllers\Customer
 */
class OrderController extends Controller
{
    /**
     * 生成订单
     *
     * @param Request      $request
     * @param OrderService $service
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function generateOrder(Request $request, OrderService $service)
    {
        $this->validate(
            $request,
            [
                'address_id'       => 'required|exists:shipping_address,id',
                'goods'            => 'required|array',
                'goods.*.goods_id' => 'required|exists:main_mysql.dp_goods_info,id',
                'goods.*.buy_num'  => 'required|integer|min:1｜max:65535',
                'payment'          => 'required|integer',
                'delivery'         => 'required|integer',
            ]
        );

        if (!in_array($request->input('payment'), Order::$paymentArr)) {
            throw new AppException("暂不支持该付款方式");
        }

        if (!in_array($request->input('delivery'), Order::$deliveryArr)) {
            throw new AppException("暂不支持该配送方式");
        }

        $newOrderId = $service->generateOrder(
            $request->input('address_id'),
            $request->input('goods'),
            $request->input('payment'),
            $request->input('delivery')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => ['id' => $newOrderId],
        ]);
    }


    /**
     * 订单列表
     *
     * @param \Illuminate\Http\Request   $request
     * @param \App\Services\OrderService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderList(Request $request, OrderService $service)
    {
        $queryStatus = '0,1,2,3';
        $this->validate(
            $request,
            [
                'status' => 'integer|in:' . $queryStatus,
                'page'   => 'required|integer|min:1',
                'size'   => 'required|integer|between:1,20',
            ],
            [
                'status.integer' => '查询状态必须是一个整型',
                'status.in'      => '查询状态必须为:' . $queryStatus . '中的数字',

                'page.required' => '获取页数必须有',
                'page.integer'  => '获取页数必须是一个整型',
                'page.min'      => '获取页数不可小于:min',

                'size.required' => '获取数据量必须有',
                'size.integer'  => '获取数据量必须是一个整型',
                'size.between'  => '获取数据量必须是:min到:max',
            ]
        );
        $userInfo = getUser();
        $status = $request->input('status', 0);
        $listArr = $service->getOrderList($status, $request->input('page'),
            $request->input('size'), $userInfo);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $listArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 订单状态修改
     *
     * @param \Illuminate\Http\Request   $request
     * @param \App\Services\OrderService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderStatus(Request $request, OrderService $service)
    {
        $this->validate(
            $request,
            [
                'id'     => 'required|integer|min:1|exists:orders,id',
                'status' => [
                    'required',
                    'integer',
                    Rule::in(Order::$statusArr),
                ],
            ],
            [
                'id.required' => '订单ID必须有',
                'id.integer'  => '订单ID必须是一个整型',
                'id.min'      => '订单ID不可小于:min',
                'id.exists'   => '订单不存在',

                'status.required' => '操作状态必须有',
                'status.integer'  => '操作状态必须是一个整型',
                'status.in'       => '操作状态必须为:' .
                                     implode(',', Order::$statusArr) . '中的值',
            ]
        );

        $userInfo = getUser();
        $service->updateOrderStatus(
            $request->input('id'),
            $request->input('status'),
            OrderLog::CUSTOMER,
            $userInfo
        );

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 订单支付
     *
     * @param \Illuminate\Http\Request   $request
     * @param \App\Services\OrderService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment(Request $request, OrderService $service)
    {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1|exists:orders,id,deleted_at,NULL',
            ],
            [
                'id.required' => '订单ID必须有',
                'id.integer'  => '订单ID必须是一个整型',
                'id.min'      => '订单ID不可小于:min',
                'id.exists'   => '订单不存在',
            ]
        );
        $userInfo = getUser();
        $paymentConfigArr = $service->payment($request->input('id'), $userInfo);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $paymentConfigArr,
        ];

        return response()->json($reDataArr);
    }

    /**
     * 订单支付查询处理
     * 主要是处理前端发起支付失败的订单进行主动查询处理
     *
     * @param \Illuminate\Http\Request            $request
     * @param \App\Workflows\PaymentQueryWorkflow $queryWorkflow
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentQuery(
        Request $request,
        PaymentQueryWorkflow $queryWorkflow
    ) {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1|exists:orders,id,deleted_at,NULL',
            ],
            [
                'id.required' => '订单ID必须有',
                'id.integer'  => '订单ID必须是一个整型',
                'id.min'      => '订单ID不可小于:min',
                'id.exists'   => '订单不存在',
            ]
        );
        $paymentQueryArr =
            $queryWorkflow->paymentQueryByOrderId($request->input('id'));

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $paymentQueryArr,
        ];

        return response()->json($reDataArr);
    }
}
