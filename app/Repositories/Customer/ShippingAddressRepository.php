<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 12:03
 */

namespace App\Repositories\Customer;

use App\Repositories\Customer\Contracts\ShippingAddressRepository as ContractShippingAddress;
use App\Models\ShippingAddress;

/**
 * 会员收货地址操作，实现相应的接口
 * Class ShippingAddressRepository
 * @package App\Repositories\Customer
 */
class ShippingAddressRepository implements ContractShippingAddress
{
    /**
     * 添加收货信息
     *
     * @param array $shippingArr array 收货信息数组
     *
     *                           [
     *                              receiver 收货人
     *                              province_id  地址所在省ID
     *                              city_id  地址所在市ID
     *                              county_id  地址所在区县ID
     *                              address 收货地址
     *                              user_id 会员ID
     *                              mobile  联系电话
     *                           ]
     *
     * @return ShippingAddress
     */
    public function shippingAddress(array $shippingArr)
    {
        return ShippingAddress::create($shippingArr);
    }

    /**
     * 根据记录ID获取收货信息
     *
     * @param       $id        integer 收货信息数组
     * @param array $selectArr array 获取字段
     *
     *                           ['select 1', ...]
     *
     * @return \App\Models\ShippingAddress
     */
    public function getInfoById($id, $selectArr)
    {
        return ShippingAddress::where('id', $id)
            ->select($selectArr)
            ->first();
    }

    /**
     * @param array $requestArr          array 待修改信息
     *
     *                          [
     *                              id  记录id
     *                              receiver 收货人
     *                              province_id  地址所在省ID
     *                              city_id  地址所在市ID
     *                              county_id  地址所在区县ID
     *                              address 收货地址
     *                              mobile  联系电话
     *                           ]
     *
     * @param       $shippingAddressInfo \App\Models\ShippingAddress Model
     *
     * @return \App\Models\ShippingAddress
     */
    public function updateAddress(array $requestArr, $shippingAddressInfo)
    {
        $shippingAddressInfo->receiver = $requestArr['receiver'];
        $shippingAddressInfo->province_id = $requestArr['province_id'];
        $shippingAddressInfo->city_id = $requestArr['city_id'];
        $shippingAddressInfo->county_id = $requestArr['county_id'];
        $shippingAddressInfo->address = $requestArr['address'];
        $shippingAddressInfo->mobile = $requestArr['mobile'];
        $shippingAddressInfo->save();

        return $shippingAddressInfo;
    }

    /**
     * 删除收货信息
     *
     * @param $id integer 记录ID
     *
     * @return integer
     */
    public function delAddress($id)
    {
        return ShippingAddress::where('id', $id)
            ->delete();
    }
}
