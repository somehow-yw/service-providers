<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Area
 *
 * @package App\Models
 * @property int    $id      区域id
 * @property int    $pid     父级id
 * @property string $node    所有的父级id串
 * @property string $name    区域名字
 * @property float  $lat     纬度
 * @property float  $lng     经度
 * @property int    $level   区域等级
 * @property int    $status  当前区域开通状态
 * @property Area   $parent  关联自身父级信息
 */
class Area extends Model
{
    // 区域等级 level
    const LEVEL_COUNTRY = 0;  // 国家
    const LEVEL_PROVINCE = 10; // 省级
    const LEVEL_CITY = 20; // 市级
    const LEVEL_DISTRICT = 30; // 区级
    const LEVEL_STREET = 40; // 街道

    // 街道状态 status
    const STATUS_NORMAL = 1;    // 已开通
    const STATUS_CLOSED = 0;    // 未开通

    public $incrementing = false;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'area';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',       // ID
        'pid',      // 父级ID
        'node',     // 节点数据，由其所有父级节点ID组成, 顶级节点则为空 以（,）号分隔
        'name',     // 区域名字
        'lat',      // 纬度
        'lng',      // 经度
        'level',    // 区域等级 0:国家 10:省 20:市 30:区县 40:街道
        'status',   // 状态：是否已开通 0: 未开通 1: 已开通
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    // 关联父级信息
    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'pid');
    }

    /**
     * 返回后台管理需要个格式
     *
     * @return array
     */
    public function formatForAdmin()
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'district' => $this->parent->name,
            'status'   => $this->status,
        ];
    }

    /**
     * 获取区域名称
     *
     * @param $id integer 区域ID
     *
     * @return string
     */
    public static function getName($id)
    {
        $areaModel = self::query()->where('id', $id)->first();
        if ($areaModel) {
            return $areaModel->name;
        } else {
            return "";
        }
    }
}
