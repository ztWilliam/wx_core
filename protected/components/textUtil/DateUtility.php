<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhengtuoqingdao
 * Date: 12-8-21
 * Time: 下午4:27
 * To change this template use File | Settings | File Templates.
 */
class DateUtility
{
    /*
    * 指定两个时间段，返回不同的时间数
    * $interval：只允许intervals有以下五个值："w"(周)、"d"（天）、"h"（小时）、"m"（分钟） 和"s"（秒）
    * $date1 通常为当前时间；
    * $date2 需要计算的时间；
    * date2 - date1然后转换成相应的数据类型
    */
    public static  function DateDiff( $date1, $date2,$interval = "d", $abs = true)
    {
        // 得到两日期之间间隔的秒数
        $timeDifference = strtotime($date2) - strtotime($date1);
        switch ($interval) {
            case "w":
                $retVal = bcdiv($timeDifference, 604800);
                break;
            case "d":
                $retVal = bcdiv($timeDifference, 86400);
                break;
            case "h":
                $retVal = bcdiv($timeDifference, 3600);
                break;
            case "m":
                $retVal = bcdiv($timeDifference, 60);
                break;
            case "s":
                $retVal = $timeDifference;
                break;
        }

        if($abs)
            return abs($retVal);
        else
            return $retVal;
    }
}
