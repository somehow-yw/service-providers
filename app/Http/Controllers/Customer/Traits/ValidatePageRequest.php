<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 10:47
 */

namespace App\Http\Controllers\Customer\Traits;

use App\Exceptions\AppException;
use Illuminate\Http\Request;
use App\Exceptions\Customer\CustomerException;
use Validator;

/**
 * 用户前端请求验证扩展
 * Class ValidatePageRequest
 * @package App\Http\Controllers\Customer\Traits
 */
trait ValidatePageRequest
{
    /**
     * 验证店铺信息
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws CustomerException|AppException
     */
    public function validateShopInfo(Request $request)
    {
        $requestInfoArr = $request->all();
        if (count($requestInfoArr) <= 0 || !is_array($requestInfoArr)) {
            throw new AppException('信息未传入');
        }
        foreach ($requestInfoArr as $validateArr) {
            switch ($validateArr['type']) {
                case 'shop_name':
                    $this->validateShopName($validateArr);
                    break;
                case 'mobile_phone':
                    $this->validateLinkMobile($validateArr);
                    break;
                case 'shop_type_id':
                    $this->validateShopType($validateArr);
                    break;
                default:
                    throw new CustomerException(CustomerException::OPTION_TYPE_NOT);
            }
        }
    }

    /**
     * 店铺名的验证
     *
     * @param array $validateArr array 验证数组
     *
     * @throws AppException
     */
    public function validateShopName(array $validateArr)
    {
        $validator = Validator::make(
            $validateArr,
            [
                'value' => 'required|string|between:3,50',
            ],
            [
                'value.required' => '店铺名不能为空',
                'value.string'   => '店铺名必须是字符串类型',
                'value.between'  => '店铺名应在:min到:max个字符',
            ]
        );
        if ($validator->fails()) {
            throw new AppException($validator->errors()->first());
        }
    }

    /**
     * 验证联系手机
     *
     * @param array $validateArr array 验证数组
     *
     * @throws AppException
     */
    public function validateLinkMobile(array $validateArr)
    {
        $validator = Validator::make(
            $validateArr,
            [
                'value' => 'required|mobile',
            ],
            [
                'value.required' => '联系手机号码不能为空',
                'value.mobile'   => '联系手机号码不正确',
            ]
        );
        if ($validator->fails()) {
            throw new AppException($validator->errors()->first());
        }
    }

    /**
     * 验证店铺类型
     *
     * @param array $validateArr array 验证数组
     *
     * @throws AppException
     */
    public function validateShopType(array $validateArr)
    {
        $validator = Validator::make(
            $validateArr,
            [
                'value' => 'required|integer|min:1|exists:shop_type,id',
            ],
            [
                'value.required' => '店铺类型不能为空',
                'value.integer'  => '店铺类型必须是一个整数',
                'value.min'      => '店铺类型不能小于:min',
                'value.exists'   => '店铺类型不存在',
            ]
        );
        if ($validator->fails()) {
            throw new AppException($validator->errors()->first());
        }
    }
}
