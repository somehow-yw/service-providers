<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Models\AuthenticatedUser;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Customer\Traits\ValidatePageRequest;

/**
 * Class CustomController.
 * 会员信息操作
 *
 * @package App\Http\Controllers\Custom
 */
class CustomerController extends Controller
{
    use ValidatePageRequest;

    /**
     * 获取服务商联系方式
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function getContactInfo()
    {
        $serviceProvider = getServiceProvider();
        if (empty($serviceProvider)) {
            throw new AppException("该服务商信息有待完善");
        }

        $info = $serviceProvider->info;

        if (empty($info)) {
            $info = [
                'avatar'          => '',
                'delivery_remark' => '',
                'introduction'    => '',
            ];
        } else {
            $info = [
                'avatar'          => $info->avatar,
                'delivery_remark' => $info->delivery_remark,
                'introduction'    => $info->introduction,
            ];
        }

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => array_merge([
                    'shop_name' => $serviceProvider->shop_name,
                    'address'   => $serviceProvider->address,
                    'mobile'    => $serviceProvider->mobile,
                ], $info),
            ]
        );
    }


    /**
     * 发送验证码
     *
     * @param Request         $request
     *
     * @param CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVerify(Request $request, CustomerService $service)
    {
        $this->validate(
            $request,
            [
                'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
            ],
            [
                'mobile.required' => '手机号不能为空',
                'mobile.regex'    => '请输入正确的手机号',
            ]
        );

        $service->sendVerify($request->input('mobile'));

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => [],
            ]
        );
    }

    /**
     * 服务商客户注册第一步
     *
     * @param Request         $request
     * @param CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register1(Request $request, CustomerService $service)
    {
        $this->validate(
            $request,
            [
                'mobile' => 'required|regex:/^1[34578][0-9]{9}$/',
                'verify' => 'required|regex:/^[0-9]{4,4}$/',
            ],
            [
                'mobile.required' => '手机号不能为空',
                'mobile.regex'    => '请输入正确的手机号',

                'verify.required' => '验证码不能为空',
                'verify.regex'    => '验证码由4位数字组成',
            ]
        );

        $service->register1(
            $request->input('mobile'),
            $request->input('verify')
        );

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => [],
            ]
        );
    }

    /**
     * 服务商客户注册第二步
     *
     * @param Request         $request
     * @param CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register2(Request $request, CustomerService $service)
    {
        $this->validate(
            $request,
            [
                'shop_name'    => 'required|string|max:30',
                'province_id'  => 'required|integer|exists:area,id',
                'city_id'      => 'required|integer|exists:area,id',
                'county_id'    => 'integer|exists:area,id',
                'address'      => 'required|string|max:150',
                'shop_type_id' => 'required|integer|exists:shop_type,id',
            ],
            [
                'shop_name.required' => '店铺名不能为空',
                'shop_name.string'   => '店铺名应该是个字符串',
                'shop_name.max'      => '店铺名不能超过:max个字符',

                'province_id.required' => '省不能为空',
                'province_id.exists'   => '省不存在',

                'city_id.required' => '市不能为空',
                'city_id.exists'   => '市不存在',

                'county_id.exists' => '区县不存在',

                'address.required' => '收货地址不能为空',
                'address.string'   => '收货地址是字符串',
                'address.max'      => '收货地址不能超过:max个字符',

                'shop_type_id.required' => '店铺类型不能为空',
                'shop_type_id.exists'   => '店铺类型不存在',
            ]
        );

        $service->register2($request->only(
            [
                'shop_name',
                'province_id',
                'city_id',
                'county_id',
                'address',
                'shop_type_id',
            ]
        ));

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => [],
            ]
        );
    }

    /**
     * 获取注册状态
     *
     * @param CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegisterStatus(CustomerService $service)
    {
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'status' => $service->getRegisterStatus(),
            ],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 服务商客户设置收货地址
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Services\CustomerService $service
     * @param \App\Models\AuthenticatedUser $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAddress(
        Request $request,
        CustomerService $service,
        AuthenticatedUser $user
    ) {
        $this->validate(
            $request,
            [
                'receiver'    => 'required|string|max:30',
                'province_id' => 'required|integer|min:1|exists:area,id',
                'city_id'     => 'required|integer|min:1|exists:area,id',
                'county_id'   => 'integer|min:1|exists:area,id',
                'address'     => 'required|string|max:150',
                'mobile'      => 'required|regex:/^1[34578][0-9]{9}$/',
            ],
            [
                'receiver.required' => '收货人不能为空',
                'receiver.string'   => '收货人应该是个字符串',
                'receiver.max'      => '收货人不能超过:max个字符',

                'province_id.required' => '省不能为空',
                'city_id.required'     => '市不能为空',

                'county_id.integer' => '县ID只能是整型',
                'county_id.min'     => '县ID不可小于:min',
                'county_id.exists'  => '县ID不存在',

                'address.required' => '收货地址不能为空',
                'address.string'   => '收货地址是字符串',
                'address.max'      => '收货地址不能超过:max个字符',

                'mobile.required' => '手机号不能为空',
                'mobile.regex'    => '请输入正确的手机号',
            ]
        );
        $userInfo = $user->getUser();
        $service->shippingAddress($request->all(), $userInfo->id);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 服务商客户修改已有收货地址
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Services\CustomerService $service
     * @param \App\Models\AuthenticatedUser $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(
        Request $request,
        CustomerService $service,
        AuthenticatedUser $user
    ) {
        $this->validate(
            $request,
            [
                'id'          => 'required|integer|min:1|exists:shipping_address,id',
                'receiver'    => 'required|string|max:30',
                'province_id' => 'required|integer|min:1|exists:area,id',
                'city_id'     => 'required|integer|min:1|exists:area,id',
                'county_id'   => 'integer|min:1|exists:area,id',
                'address'     => 'required|string|max:150',
                'mobile'      => 'required|regex:/^1[34578][0-9]{9}$/',
            ],
            [
                'id.required' => '修改记录ID不能为空',
                'id.integer'  => '修改记录ID必须是一个整数',
                'id.min'      => '修改记录ID不可小于:min',
                'id.exists'   => '记录ID不存在',

                'receiver.required' => '收货人不能为空',
                'receiver.string'   => '收货人应该是个字符串',
                'receiver.max'      => '收货人不能超过:max个字符',

                'province_id.required' => '省不能为空',
                'city_id.required'     => '市不能为空',
                'county_id.required'   => '县不存在',

                'address.required' => '收货地址不能为空',
                'address.string'   => '收货地址是字符串',
                'address.max'      => '收货地址不能超过:max个字符',

                'mobile.required' => '手机号不能为空',
                'mobile.regex'    => '请输入正确的手机号',
            ]
        );
        $userInfo = $user->getUser();
        $service->updateAddress($request->all(), $userInfo->id);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 删除收货地址信息
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Services\CustomerService $service
     * @param \App\Models\AuthenticatedUser $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delAddress(
        Request $request,
        CustomerService $service,
        AuthenticatedUser $user
    ) {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1|exists:shipping_address,id',
            ],
            [
                'id.required' => '删除记录ID不能为空',
                'id.integer'  => '删除记录ID必须是一个整数',
                'id.min'      => '删除记录ID不可小于:min',
                'id.exists'   => '记录不存在',
            ]
        );
        $userInfo = $user->getUser();
        $service->delAddress($request->input('id'), $userInfo);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 更改默认收货地址
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Services\CustomerService $service
     * @param \App\Models\AuthenticatedUser $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMainAddress(
        Request $request,
        CustomerService $service,
        AuthenticatedUser $user
    ) {
        $this->validate(
            $request,
            [
                'id' => 'required|integer|min:1|exists:shipping_address,id',
            ],
            [
                'id.required' => '记录ID不能为空',
                'id.integer'  => '记录ID必须是一个整数',
                'id.min'      => '记录ID不可小于:min',
                'id.exists'   => '记录不存在',
            ]
        );
        $userInfo = $user->getUser();
        $service->updateMainAddress($request->input('id'), $userInfo);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 客户店铺信息修改
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Models\AuthenticatedUser $user
     * @param \App\Services\CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShopInfo(
        Request $request,
        AuthenticatedUser $user,
        CustomerService $service
    ) {
        $this->validateShopInfo($request);

        $userInfo = $user->getUser();
        $service->updateInfo($request->all(), $userInfo);

        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => [],
        ];

        return response()->json($reDataArr);
    }

    /**
     * 获取客户信息
     *
     * @param CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(CustomerService $service)
    {
        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => $service->getProfile(),
            ]
        );
    }

    /**
     * 获取当前会员已有收货地址
     *
     * @param \App\Services\CustomerService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShippingAddresses(CustomerService $service)
    {
        $userInfo = getUser();
        $dataArr = $service->getShippingAddresses($userInfo);
        $reDataArr = [
            'code'    => 0,
            'message' => 'OK',
            'data'    => $dataArr,
        ];

        return response()->json($reDataArr);
    }
}
