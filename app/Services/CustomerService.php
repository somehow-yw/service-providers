<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-1
 * Time: 下午6:15
 */

namespace App\Services;

use App;
use App\Exceptions\AppException;
use App\Exceptions\Customer\CustomerException;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\WechatAccount;
use App\Repositories\Customer\Contracts\ShippingAddressRepository;
use App\Repositories\Customer\Contracts\UserRepository;
use App\Utils\GenerateRandomNumber;
use Carbon\Carbon;
use DB;
use Zdp\Mobile\Models\MobileVerify;
use Zdp\Mobile\Services\Mobile;
use Zdp\ServiceProvider\Data\Utils\UserTag;

/**
 * Class CustomerService.
 * 会员信息操作
 * @package App\Services\CustomerService
 */
class CustomerService
{
    private $shippingAddressRepo;
    private $userRepo;

    /**
     * CustomerService constructor.
     *
     * @param ShippingAddressRepository $_shippingAddressRepo
     * @param UserRepository            $userRepo
     */
    public function __construct(ShippingAddressRepository $_shippingAddressRepo, UserRepository $userRepo)
    {
        $this->shippingAddressRepo = $_shippingAddressRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * 获取用户注册状态
     * @return integer
     */
    public function getRegisterStatus()
    {
        $user = getUser();
        if (empty($user)) {
            $status = User::NOT_REGISTER;
        } elseif (empty($user->shop_name)) {
            $status = User::NOT_PERFECT;
        } else {
            $status = $user->status;
        }

        return $status;
    }

    /**
     * 发送验证码
     *
     * @param $mobile
     *
     * @throws AppException
     */
    public function sendVerify($mobile)
    {
        $this->mobileIsUsed($mobile);
        $today = Carbon::today()->format('Y-m-d H:i:s');
        $sendCount = MobileVerify::where('mobile', $mobile)->where('created_at', '>', $today)->count();

        $MAX_SEND_COUNT_PER_DAY = 4;
        if ($sendCount > $MAX_SEND_COUNT_PER_DAY) {
            throw new AppException("每天发送验证码次数达上线");
        }
        // 验证码
        $verificationCode = GenerateRandomNumber::generateString(4, '0123456789');
        ///** @var GenRestSms $mobileService */
        //$mobileService = app(GenRestSms::class);
        $source = resolveSource();
        $wechatInfo = WechatAccount::getWeChatConfig($source);
        $dataArr = [
            $wechatInfo['wechat_name'],
            $verificationCode,
            '5分钟',  // 过期时间
        ];
        //$tempId = 163580;//44043
        //$mobileService->construct()->genSendTemplateSmsData($mobile, $dataArr, $tempId)->curlPost();
        /** @var Mobile $mobileService */
        $mobileService = app(Mobile::class);
        //$mobileService->sendVerify($mobile);
        $mobileService->sendCustomVerify($mobile, $dataArr, $verificationCode);
    }

    /**
     * 手机号是否被使用过
     *
     * @param $mobile
     *
     * @throws AppException
     */
    protected function mobileIsUsed($mobile)
    {
        $weChatAccount = getWeChatAccount();
        $count = User::where('mobile_phone', $mobile)
            ->where('sp_id', $weChatAccount->sp_id)
            ->count();
        if ($count > 0) {
            throw new AppException("该手机号已经被使用了");
        }
    }

    /**
     * 注册服务商客户
     *
     * @param $mobile
     * @param $verify
     *
     * @throws AppException
     */
    public function register1($mobile, $verify)
    {
        $status = $this->getRegisterStatus();
        if (User::NOT_REGISTER !== $status) {
            if (User::NOT_PERFECT === $status) {
                throw new AppException("你已经注册，请进一步完善信息");
            } else {
                throw new AppException("你已经注册成功");
            }
        }
        $this->mobileIsUsed($mobile);
        $weChatAccounts = getWeChatAccount();
        $this->validateVerify($mobile, $verify);
        $this->userRepo->createUser(
            getWeChatOAuthUser(),
            $mobile,
            $weChatAccounts->sp_id
        );
    }

    /**
     * 注册第二步 完善信息
     *
     * @param $requestArr               array [
     *                                  'shop_name',     店铺名 同时也是第一个收货地址的联系人
     *                                  'province_id',   省
     *                                  'city_id',       市
     *                                  'county_id',     县users
     *                                  'address',       收货地址
     *                                  'shop_type_id'   店铺类型id
     *                                  ]
     *
     * @throws AppException
     */
    public function register2(array $requestArr)
    {
        $status = $this->getRegisterStatus();
        if (User::NOT_REGISTER === $status) {
            throw new AppException("请先填写手机号");
        }
        if (User::NOT_PERFECT !== $status) {
            throw new AppException("你已经注册成功");
        }

        $user = getUser();

        $updateUserArr = array_filter($requestArr, function ($value) {
            return !empty($value);
        });

        $updateAddressArr = array_merge(
            array_except($requestArr, 'shop_name'),
            [
                'user_id'  => $user->id,
                'mobile'   => $user->mobile_phone,
                'receiver' => $requestArr['shop_name'],
            ]
        );

        $updateAddressArr = array_filter($updateAddressArr, function ($value) {
            return !empty($value);
        });

        $addressModel = $this->shippingAddressRepo->shippingAddress($updateAddressArr);

        $userTag = new UserTag(config('wechat'));
        $openId = getWeChatOAuthUser()->getId();

        DB::transaction(function () use ($user, $userTag, $openId, $updateUserArr, $addressModel) {
            $user->update($updateUserArr);
            $user->update(['shipping_address_id' => $addressModel->id]);
            if (!config('wechat.enable_mock')) {
                $userTag->tagSigned($openId);
            }
        });
    }

    /**
     * 校验验证码
     *
     * @param $mobile
     * @param $verify
     *
     * @throws AppException
     */
    protected function validateVerify($mobile, $verify)
    {
        $limit = Carbon::now()->subMinutes(config('mobile.verify.expired'));
        $verify = MobileVerify::where('mobile', $mobile)
            ->where('code', $verify)
            ->where('updated_at', '>=', $limit)
            ->first();

        if (empty($verify)) {
            throw new AppException("验证码错误");
        }
    }

    /**
     * 服务商客户设置收货地址
     *
     * @param array $requestArr array 收货地址信息
     *
     *                          [
     *                              receiver 收货人
     *                              province_id  地址所在省ID
     *                              city_id  地址所在市ID
     *                              county_id  地址所在区县ID
     *                              address 收货地址
     *                              mobile  联系电话
     *                           ]
     *
     * @param       $userId     integer 会员ID
     *
     * @throws CustomerException
     * @return array
     */
    public function shippingAddress(array $requestArr, $userId)
    {
        $addressNum = ShippingAddress::query()->where('user_id', $userId)->count();
        if ($addressNum >= ShippingAddress::MAX_ADDRESS_NUM) {
            throw new CustomerException(CustomerException::ADDRESS_NUM_OVER);
        }
        $requestArr['user_id'] = $userId;
        $createDataModel = $this->shippingAddressRepo->shippingAddress($requestArr);

        return $createDataModel->toArray();
    }


    /**
     * 服务商客户修改收货地址
     *
     * @param array $requestArr array 收货地址信息
     *
     *                          [
     *                              id  修改数据ID
     *                              receiver 收货人
     *                              province_id  地址所在省ID
     *                              city_id  地址所在市ID
     *                              county_id  地址所在区县ID
     *                              address 收货地址
     *                              mobile  联系电话
     *                           ]
     *
     * @param       $userId     integer 会员ID
     *
     * @throws CustomerException
     * @return array
     */
    public function updateAddress(array $requestArr, $userId)
    {
        // 获得待修改的数据信息
        $selectArr = ['id', 'user_id'];
        $shippingAddressInfo = $this->getAddressInfoById($requestArr['id'], $selectArr);
        if ($shippingAddressInfo->user_id != $userId) {
            throw new CustomerException(CustomerException::ADDRESS_NOT_PART_YOU);
        }
        $requestArr['county_id'] = empty($requestArr['county_id']) ? 0 : $requestArr['county_id'];
        $addressDataModel = $this->shippingAddressRepo->updateAddress($requestArr, $shippingAddressInfo);

        return $addressDataModel->toArray();
    }

    /**
     * 服务商客户删除收货地址
     *
     * @param $id       integer 记录ID
     * @param $userInfo App\Models\User 会员信息
     *
     * @throws CustomerException
     * @return integer
     */
    public function delAddress($id, $userInfo)
    {
        // 获得待修改的数据信息
        $selectArr = ['id', 'user_id'];
        $shippingAddressInfo = $this->getAddressInfoById($id, $selectArr);
        if ($shippingAddressInfo->user_id != $userInfo->id) {
            throw new CustomerException(CustomerException::ADDRESS_NOT_PART_YOU);
        }
        $delNum = 0;
        DB::transaction(
            function () use ($id, $userInfo, &$delNum) {
                $delNum = $this->shippingAddressRepo->delAddress($id);
                if ($userInfo->shipping_address_id === $id) {
                    // 如果是默认收货地址，则更改默认收货地址ID为0
                    $updateArr = [
                        'shipping_address_id' => 0,
                    ];
                    $this->userRepo->updateInfoById($userInfo->id, $updateArr);
                }
            }
        );

        return $delNum;
    }

    /**
     * 服务商客户修改默认收货地址
     *
     * @param $id       integer 默认收货地址记录ID
     * @param $userInfo App\Models\User 会员信息
     *
     * @throws CustomerException
     * @return integer
     */
    public function updateMainAddress($id, $userInfo)
    {
        // 获得待修改的数据信息
        $selectArr = ['id', 'user_id'];
        $shippingAddressInfo = $this->getAddressInfoById($id, $selectArr);
        if ($shippingAddressInfo->user_id != $userInfo->id) {
            throw new CustomerException(CustomerException::ADDRESS_NOT_PART_YOU);
        }

        $updateArr = [
            'shipping_address_id' => $id,
        ];
        $updateNum = $this->userRepo->updateInfoById($userInfo->id, $updateArr);

        return $updateNum;
    }

    /**
     * 会员信息修改
     *
     * @param array $requestArr array 修改信息
     *
     *                          [
     *                              ['type'=>'修改类型 字段', 'value'=>'修改值'],...
     *                          ]
     *
     * @param       $userInfo   App\Models\User 会员信息
     *
     * @return integer
     */
    public function updateInfo(array $requestArr, $userInfo)
    {
        $updateArr = [];
        foreach ($requestArr as $valueArr) {
            $updateArr[$valueArr['type']] = $valueArr['value'];
        }

        $updateNum = $this->userRepo->updateInfoById($userInfo->id, $updateArr);

        return $updateNum;
    }

    /**
     * 获取指定ID的收货地址信息
     *
     * @param       $id        integer 记录ID
     * @param array $selectArr array 获取字段
     *
     *                         ['select 1', ...]
     *
     * @return \App\Models\ShippingAddress
     * @throws \App\Exceptions\Customer\CustomerException
     */
    private function getAddressInfoById($id, $selectArr)
    {
        $shippingAddressInfo = $this->shippingAddressRepo->getInfoById($id, $selectArr);
        if (is_null($shippingAddressInfo)) {
            throw new CustomerException(CustomerException::INFO_DEL);
        }

        return $shippingAddressInfo;
    }

    /**
     * 获取用户信息
     *
     * @return array
     */
    public function getProfile()
    {
        $user = getUser();

        $userProfile = $this->userRepo->getUserProfile($user->id);

        return $this->formatUserProfile($userProfile);
    }

    /**
     * 格式化用户信息
     *
     * @param $userProfile User
     *
     * @return array
     */
    protected function formatUserProfile($userProfile)
    {
        $userAddresses = $userProfile->addresses;
        $addressesArr = [];

        /** @var ShippingAddress $defaultAddress */
        $defaultAddress = $userProfile->defaultAddress;

        $defaultAddressId = 0;

        if (!empty($defaultAddress)) {
            $defaultAddressId = $defaultAddress->id;

            $_ = [
                'address_id'       => $defaultAddress->id,
                'province_id'      => empty($defaultAddress->province_id) ? 0 : $defaultAddress->province_id,
                'province'         => $defaultAddress->province,
                'city_id'          => empty($defaultAddress->city_id) ? 0 : $defaultAddress->city_id,
                'city'             => $defaultAddress->city,
                'county_id'        => empty($defaultAddress->county_id) ? 0 : $defaultAddress->county_id,
                'county'           => $defaultAddress->county,
                'shipping_address' => $defaultAddress->full_address,
                'receiver'         => $defaultAddress->receiver,
                'mobile'           => $defaultAddress->mobile,
                'default'          => true,
            ];

            array_push($addressesArr, $_);
        }

        /** @var ShippingAddress $userAddress */
        foreach ($userAddresses as $userAddress) {
            // skip default shipping address
            if ($defaultAddressId !== 0 && $defaultAddressId == $userAddress->id) {
                continue;
            }
            $_['address_id'] = $userAddress->id;
            $_['province_id'] = empty($userAddress->province_id) ? 0 : $userAddress->province_id;
            $_['province'] = $userAddress->province;
            $_['city_id'] = empty($userAddress->city_id) ? 0 : $userAddress->city_id;
            $_['city'] = $userAddress->city;
            $_['county_id'] = empty($userAddress->county_id) ? 0 : $userAddress->county_id;
            $_['county'] = $userAddress->county;
            $_['shipping_address'] = $userAddress->full_address;
            $_['receiver'] = $userAddress->receiver;
            $_['mobile'] = $userAddress->mobile;
            $_['default'] = false;
            array_push($addressesArr, $_);
        }

        $shop = getServiceProvider();

        return [
            'shop_name'          => $userProfile->shop_name,
            'mobile'             => $userProfile->mobile_phone,
            'shop_type'          => $userProfile->shopType->type_name,
            'shipping_addresses' => $addressesArr,
            'sp_province_id'     => $shop->province_id,
            'sp_province'        => $shop->province,
            'sp_city_id'         => $shop->city_id,
            'sp_city'            => $shop->city,
            'sp_county_id'       => $shop->county_id,
            'sp_county'          => $shop->county,
        ];
    }

    /**
     * 返回指定会员的已有收货地址
     *
     * @param $userInfo \App\Models\User 会员信息
     *
     * @return array
     */
    public function getShippingAddresses($userInfo)
    {
        $query = ShippingAddress::query()->where('user_id', $userInfo->id)
            ->select(['id', 'province_id', 'city_id', 'county_id', 'address', 'receiver', 'mobile']);
        $mainAddressId = $userInfo->shipping_address_id;
        if ($mainAddressId) {
            // 如果有默认收货地址
            $query = $query->orderBy(DB::raw("id={$mainAddressId}"), 'desc');
        }

        $data = $query->get();
        $reDataArr = [
            'main_address_id' => $mainAddressId,
            'addresses'       => [],
        ];
        if (!$data->isEmpty()) {
            foreach ($data as $key => $item) {
                $reDataArr['addresses'][$key]['address'] = $item->address;
                $reDataArr['addresses'][$key]['intact_address'] = $item->getFullAddressAttribute();
                $reDataArr['addresses'][$key]['receiver'] = $item->receiver;
                $reDataArr['addresses'][$key]['mobile'] = $item->mobile;
                $reDataArr['addresses'][$key]['id'] = $item->id;
                $reDataArr['addresses'][$key]['province_id'] = $item->province_id;
                $reDataArr['addresses'][$key]['city_id'] = $item->city_id;
                $reDataArr['addresses'][$key]['county_id'] = $item->county_id;
            }
        }

        return $reDataArr;
    }
}
