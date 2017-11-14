<?php
/**
 * Created by PhpStorm.
 * User: xty
 * Date: 2016/7/26
 * Time: 21:19
 */

namespace App\Utils;


class PhpSerializeUtil
{
    /**
     * PHP返序列化字符串
     *
     * @param string $serialStr 返序列化的字符串
     * @return object|array
     */
    public static function mb_unserialize($serialStr)
    {
        $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serialStr);

        return unserialize($out);
    }

    /**
     * PHP序列化数组或对象为字符串
     *
     * @param object|array $serializeObj 待序列化的数据
     * @return string
     */
    public static function mb_serialize($serializeObj)
    {
        return serialize($serializeObj);
    }
}