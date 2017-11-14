<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/11/15
 * Time: 9:27
 */

namespace App\Extensions\Traits;

/**
 * Class IdCardTrait.
 * 身份证号码校验
 * @package App\Extensions\Traits
 */
trait IdCardTrait
{
    /**
     * 计算18位身份证校验码，根据国家标准GB 11643-1999
     *
     * @param $idCardBase
     *
     * @return bool
     */
    protected static function idCardVerifyNumber($idCardBase)
    {
        if (strlen($idCardBase) != 17) {
            return false;
        }
        //加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        //校验码对应值
        $verifyNumberList = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checksum = 0;
        for ($i = 0; $i < strlen($idCardBase); $i++) {
            $idCardBasei = substr($idCardBase, $i, 1) * $factor[$i];
            $checksum += (int)$idCardBasei;
        }
        $mod = $checksum % 11;
        $verifyNumber = $verifyNumberList[$mod];

        return $verifyNumber;
    }

    /**
     * 将15位身份证升级到18位
     *
     * @param $idCard
     *
     * @return bool|string
     */
    protected static function idCard15To18($idCard)
    {
        if (strlen($idCard) != 15) {
            return false;
        } else {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idCard, 12, 3), ['996', '997', '998', '999']) !== false) {
                $idCard = substr($idCard, 0, 6) . '18' . substr($idCard, 6, 9);
            } else {
                $idCard = substr($idCard, 0, 6) . '19' . substr($idCard, 6, 9);
            }
        }
        $idCard = $idCard . self::idCardVerifyNumber($idCard);

        return $idCard;
    }

    /**
     * 18位身份证有效性检查
     *
     * @param $idCard
     *
     * @return bool
     */
    protected static function idCardChecksum18($idCard)
    {
        if (strlen($idCard) != 18) {
            return false;
        }
        // 省市县（6位）
        $areaNum = substr($idCard, 0, 6);
        if (!self::checkArea($areaNum)) {
            return false;
        }
        // 出生年月日（6位）
        $checkDateNum = substr($idCard, 6,8);
        if (!self::checkDate($checkDateNum)) {
            return false;
        }
        // 性别（3位）
        // $sexNum = substr($idCard, 12, 3);
        // 校验码验证
        $idCardBase = substr($idCard, 0, 17);
        if (self::idCardVerifyNumber($idCardBase) != strtoupper(substr($idCard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证城市(地区)
     *
     * @param $area
     *
     * @return bool
     */
    protected static function checkArea($area)
    {
        // 省
        $num1 = substr($area, 0, 2);
        // 市
        $num2 = substr($area, 2, 2);
        // 区、县
        $num3 = substr($area, 4, 2);
        // 根据GB/T2260—999，省市代码11到65
        if (10 < $num1 && $num1 < 66) {
            return true;
        } else {
            return false;
        }
        // TODO 对市 区进行验证
    }

    /**
     * 验证出生日期
     *
     * @param $date
     *
     * @return bool
     */
    protected static function checkDate($date)
    {
        $birthDate = date('Ymd', strtotime($date));
        $newDate = date('Ymd', time());
        if ($birthDate != $date || $newDate < $birthDate) {
            return false;
        }

        return true;
    }
}
