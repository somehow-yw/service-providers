<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 微信消息模板
 * Class WechatTemplate
 * @package App\Models
 *
 * @property string $source
 * @property string $short_id
 * @property string $template_id
 */
class WechatTemplate extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wechat_templates';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source',           // 服务商标识 二级域名
        'short_id',         // 微信模板编号 微信的模板短ID
        'template_id',      // 已添加模板的模板ID 应用模板ID
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
