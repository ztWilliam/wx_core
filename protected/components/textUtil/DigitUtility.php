<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhengtuoqingdao
 * Date: 12-9-25
 * Time: 下午3:25
 * To change this template use File | Settings | File Templates.
 */
class DigitUtility
{

    /**
     * @static
     * @param $number 处理的数字
     * @param int $point 保留小数位数
     * @return float
     */
    public static function formatDigit($number, $point = 2)
    {
        if (!is_numeric($number)) {
            return 0;
        }
        $number = floatval($number);
        if ($number == 0) {
            return 0;
        }
        if ((is_double($number) || is_float($number)) && strpos($number, '.') > -1) {
            $tNum = explode(".", $number);
            if (strlen($tNum[1]) < $point) {
                for ($i = 1; $i < $point - strlen($tNum[1]); $i++) {
                    $number .= "0";
                }
                $result = $number;
            } else {
                $result = round($number, $point);
            }
        } else {
            $number = $number . ".";
            for ($i = 1; $i <= $point; $i++) {
                $number .= "0";
            }
            $result = $number;
        }
        $rNum = explode(".", $result);
        if ($result != 0 && strlen($rNum[1]) < $point) {
            for ($i = 1; $i <= $point - strlen($rNum[1]); $i++) {
                if (strpos($result, '.') > -1) {
                    $result .= "0";
                } else {
                    $result .=".0";
                }

            }
        }
        return $result;
    }

    /**
     * @static
     * @param  $divisor
     * @param  $dividend
     * @param int $roundNum
     * @return float|string
     */
    public static function percentOf($divisor, $dividend, $roundNum = 2) {
        if (!is_numeric($dividend))
            return '-';

        if (!is_numeric($divisor))
            return '-';

        if ($divisor == 0) {
            return '-';
        }

        $result = round($dividend / $divisor * 100, 2);

        return $result . '%';
    }


}