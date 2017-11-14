<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class User.
 * 会员信息
 * @package App\Models
 *
 * @property integer    $id
 * @property string     $user_name
 * @property Collection $addresses
 * @property string     $wechat_openid
 * @property string     $shop_name
 * @property ShopType   $shopType
 * @property integer    $shop_type
 * @property string     $default_address
 * @property string     $mobile_phone
 * @property integer    $status
 * @property string     $province
 * @property string     $city
 * @property string     $county
 * @property string     $full_address
 *
 */
class User extends Model
{
    use SoftDeletes;

    // 会员状态 status
    const NOT_REGISTER = -1;         // 尚未注册
    const NOT_PERFECT  = 0;          // 信息待完善
    const ENDING       = 1;          // 待核中
    const PASS         = 2;          // 通过
    const DENY         = 3;          // 拒绝

    /**
     * 状态数组
     *
     * @var array
     */
    protected static $statusArr = [
        self::NOT_PERFECT => '信息待完善',
        self::ENDING      => "待核中",
        self::PASS        => "通过",
        self::DENY        => "拒绝",
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sp_id',                        // 服务商ID
        'wechat_openid',                // 会员关注当前公众号的微信OPENID
        'wechat_nickname',              // 微信昵称
        'wechat_avatar',                // 微信头像
        'mobile_phone',                 // 注册手机号
        'user_name',                    // 会员真实姓名
        'shop_name',                    // 店铺名称
        'shop_type_id',                 // 店铺类型对应ID
        'province_id',                  // 所在省对应ID
        'city_id',                      // 所在市对应ID
        'county_id',                    // 所在县对应ID
        'address',                      // 所在地址
        'status',                       // 会员状态
        'shipping_address_id',          // 会员默认收货地址ID
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    // ===============
    //  关联关系
    // ===============
    /**
     * 收货地址
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(ShippingAddress::class, 'user_id', 'id');
    }


    /**
     * 默认收货地址
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function defaultAddress()
    {
        return $this->hasOne(ShippingAddress::class, 'id', 'shipping_address_id');
    }

    /**
     * 店铺类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function shopType()
    {
        return $this->belongsTo(ShopType::class, 'shop_type_id', 'id');
    }

    // ============
    //  方法定义
    // ============
    /**
     * 状态的获取
     *
     * @param $status_num
     *
     * @return string
     */
    public static function getStatus($status_num)
    {
        return self::$statusArr[$status_num] ? : '';
    }

    /**
     * 获取收货地址省
     * @return string
     */
    public function getProvinceAttribute()
    {
        $provinceModel = Area::where('id', $this->province_id)->first();
        if ($provinceModel) {
            return $provinceModel->name;
        } else {
            return "";
        }
    }

    /**
     * 获取收货地址市
     * @return string
     */
    public function getCityAttribute()
    {
        $cityModel = Area::where('id', $this->city_id)->first();
        if ($cityModel) {
            return $cityModel->name;
        } else {
            return "";
        }
    }

    /**
     * 获取收货地址区县
     * @return string
     */
    public function getCountyAttribute()
    {
        $countyModel = Area::where('id', $this->county_id)->first();
        if ($countyModel) {
            return $countyModel->name;
        } else {
            return "";
        }
    }

    /**
     * 完整的收货地址
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return $this->province . $this->city . $this->county . $this->address;
    }
}
