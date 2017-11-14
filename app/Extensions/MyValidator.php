<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/6/29
 * Time: 14:14
 *
 * @desc 扩展验证类
 */

namespace App\Extensions;

use App\Extensions\Traits\IdCardTrait;
use Illuminate\Validation\Validator;

class MyValidator extends Validator
{
    use IdCardTrait;

    /**
     * 手机号的验证
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public static function validateMobile($attribute, $value, $parameters)
    {
        if (preg_match("/^1[34578]{1}\d{9}$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 身份证号码有效性检验
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public static function validateIdCard($attribute, $value, $parameters)
    {
        $idCard = str_replace(' ', '', (string)$value);
        if (strlen($idCard) === 18) {
            return self::idCardChecksum18($idCard);
        } elseif ((strlen($idCard) === 15)) {
            $idCard = self::idCard15To18($idCard);

            return self::idCardChecksum18($idCard);
        } else {
            return false;
        }
    }

    /**
     * 中文名的验证
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     */
    public static function validateChineseName($attribute, $value, $parameters)
    {
        $name = $value;
        $nameLen = strlen($name);
        if ($nameLen < 2 || $nameLen > 30) {
            return false;
        }
        if (preg_match('/^[\x{4e00}-\x{9fa5}]{1}([·]?[\x{4e00}-\x{9fa5}]{0,5}){0,3}([\x{4e00}-\x{9fa5}])+$/u', $name)) {
            return true;
        } else {
            return false;
        }
    }
}
