<?php
/**
 * 用来记录一些公用的方法、常量等等
 * User: besterChen
 * Date: 12-7-2
 * Time: 上午11:50
 * To change this template use File | Settings | File Templates.
 */
class CommonFunction
{
    public static function create_guid()
    {

        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        //        $hyphen = chr(45);  // "-"
        //        $uuid = chr(123)    // "{"
        //        .substr($charid, 0, 8).$hyphen
        //        .substr($charid, 8, 4).$hyphen
        //        .substr($charid,12, 4).$hyphen
        //        .substr($charid,16, 4).$hyphen
        //        .substr($charid,20,12)
        //        .chr(125);// "}"
        return $charid;
    }

    /*
    * 指定两个时间段，返回不同的时间数
    * $interval：只允许intervals有以下五个值："w"(周)、"d"（天）、"h"（小时）、"m"（分钟） 和"s"（秒）
    * $date1 通常为当前时间；
    * $date2 需要计算的时间；
    * date2 - date1然后转换成相应的数据类型
    */
    public static function DateDiff ($interval = "d", $date1, $date2) {
        // 得到两日期之间间隔的秒数
        $timedifference = strtotime($date2) - strtotime($date1);
        switch ($interval) {
            case "w": $retval = bcdiv($timedifference ,604800); break;
            case "d": $retval = bcdiv( $timedifference,86400); break;
            case "h": $retval = bcdiv ($timedifference,3600); break;
            case "m": $retval = bcdiv( $timedifference,60); break;
            case "s": $retval = $timedifference; break;
        }
        return $retval;
    }

    /**
     * 判断指定的参数是否是数字
     * @static
     * @param $strArg
     */
    public static function isNumber ($strArg){
        if(!empty($strArg) && preg_match("/^[0-9]+$/", $strArg)){
            return true;
        }

        return false;
    }

}
