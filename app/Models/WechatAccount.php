<?php

namespace App\Models;

use App\Exceptions\WeChat\WeChatException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;

/**
 * Class WechatAccount.
 * 服务商微信公众号信息
 *
 * @package App\Models
 *
 * @property integer $sp_id
 * @property string  source
 * @property string  $wechat_name
 * @property string  $appid
 * @property string  $secret
 * @property string  $token
 * @property string  $aes_key
 */
class WechatAccount extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wechat_accounts';

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
        'sp_id',            // 服务商ID
        'source',           // 来源站，为服务商指定的特定域名或其它可验证信息
        'wechat_name',      // 公众号名称
        'appid',            // 服务商公众号应用ID AppID
        'secret',           // 服务商公众号应用密钥 AppSecret
        'token',            // 服务商公众号令牌 Token
        'aes_key',          // 服务商公众号消息加解密密钥 EncodingAESKey
    ];

    /**
     * 主键的设置
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 服务商model关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class, 'zdp_user_id', 'sp_id');
    }

    /**
     * 获得当前服务商的微信公众号信息
     *
     * @param string $source
     *
     * @return array
     * @throws \App\Exceptions\WeChat\WeChatException
     */
    public static function getWeChatConfig($source)
    {
        $weChatInfoCollection = self::where('source', $source)->first();
        if (is_null($weChatInfoCollection)) {
            throw new WeChatException(WeChatException::SERVICE_PROVIDERS_NOT);
        }

        return [
            'app_id'      => $weChatInfoCollection->appid,
            'secret'      => $weChatInfoCollection->secret,
            'token'       => $weChatInfoCollection->token,
            'aes_key'     => $weChatInfoCollection->aes_key,
            'wechat_name' => $weChatInfoCollection->wechat_name,
        ];
    }
}
