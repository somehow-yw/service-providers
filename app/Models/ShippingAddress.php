<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShippingAddress.
 * 会员收货地址
 *
 * @package App\Models
 *
 * @property integer $id
 * @property string  $county_id
 * @property string  $city_id
 * @property string  $province_id
 * @property string  $province
 * @property string  $city
 * @property string  $county
 * @property string  $address
 * @property string  $receiver
 * @property string  $mobile
 * @property string  $full_address
 *
 */
class ShippingAddress extends Model
{
    use SoftDeletes;

    // 每个会员最多可设置的收货地址数量
    const MAX_ADDRESS_NUM = 10;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shipping_address';

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
        'user_id',            // 用户id
        'province_id',        // 所在省对应ID
        'city_id',            // 所在市对应ID
        'county_id',          // 所在县对应ID
        'address',            // 收货地址
        'receiver',           // 收货人
        'mobile',             // 收货人电话
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
     * 会员信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 省
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provinceRelation()
    {
        return $this->hasOne(Area::class, 'id', 'province_id');
    }

    /**
     * 市
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cityRelation()
    {
        return $this->hasOne(Area::class, 'id', 'city_id');
    }

    /**
     * 县
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function countyRelation()
    {
        return $this->hasOne(Area::class, 'id', 'county_id');
    }

    /**
     * 获取收货地址省
     *
     * @return string
     */
    public function getProvinceAttribute()
    {
        $provinceModel = $this->provinceRelation;
        if ($provinceModel) {
            return $provinceModel->name;
        } else {
            return "";
        }
    }

    /**
     * 获取收货地址市
     *
     * @return string
     */
    public function getCityAttribute()
    {
        $cityModel = $this->cityRelation;
        if ($cityModel) {
            return $cityModel->name;
        } else {
            return "";
        }
    }

    /**
     * 获取收货地址区县
     *
     * @return string
     */
    public function getCountyAttribute()
    {
        $countyModel = $this->countyRelation;
        if ($countyModel) {
            return $countyModel->name;
        } else {
            return "";
        }
    }

    /**
     * 完整的收货地址
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return $this->province . $this->city . $this->county . $this->address;
    }
}
