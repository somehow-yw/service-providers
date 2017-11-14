<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 11:20
 */

namespace App\Repositories\Customer\Contracts;

/**
 * 会员收货地址操作
 * Interface ShippingAddressRepository.
 * @package App\Repositories\Customer\Contracts
 */
interface ShippingAddressRepository
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
     * @return \App\Models\ShippingAddress
     */
    public function shippingAddress(array $shippingArr);

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
    public function getInfoById($id, $selectArr);

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
    public function updateAddress(array $requestArr, $shippingAddressInfo);

    /**
     * 删除收货信息
     *
     * @param $id integer 记录ID
     *
     * @return integer
     */
    public function delAddress($id);
}
