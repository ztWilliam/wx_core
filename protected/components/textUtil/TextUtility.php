<?php
/**
 * Description:
 * put the description here.
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 12-7-3
 * Time: 下午9:42
 *
 */

class TextUtility
{


    /**
     * 获取字符串
     * @static
     * @param $index
     * @param $delimiter
     * @param $str
     * @return string
     */
    private static function getElementAt($index, $delimiter, $str)
    {
        if (!empty($str)) {
            $str_trimed = trim(trim($str), $delimiter);

            $pArr = explode($delimiter, $str_trimed);
            return trim($pArr[$index]);
        }

        return '';
    }

    public static function getFirstElement($str, $delimiter)
    {
        return self::getElementAt(0, $delimiter, $str);
    }

    public static function getLastElement($str, $delimiter)
    {
        if (!empty($str)) {

            $str_trimed = trim(trim($str), $delimiter);

            $pArr = explode($delimiter, $str_trimed);
            return trim($pArr[count($pArr) - 1]);
        }

        return '';
    }

    /**
     * 按照长度截取字符串
     * @static
     * @param $str
     * @param int $len
     * @param string $char
     * @return string
     */
    public static function getStringByLength($str, $len = 100, $char = 'UTF8')
    {

        if (mb_strlen($str, 'utf8') <= $len) {
            return $str;
        }
        return mb_substr($str, 0, $len, $char) . "...";
    }

    /**
     * 按照字节截取字符串
     * @static
     * @param $str
     * @param int $len
     * @param string $char
     * @return string
     */
    public static function getStringByByte($str, $len = 100, $char = 'UTF8')
    {

        if (strlen($str) <= $len) {
            return $str;
        }
        $i = 0;
        $p = 0;
        while ($i < $len) {
            $s = mb_substr($str, $p, 1, $char);

            $slen = (strlen($s) == 3) ? 2 : strlen($s); //如果是汉字的话给他2个长度单位，但愿对汉字的判断能够准确
            $p++;
            $i += $slen;
        }
        return mb_substr($str, 0, $p, $char) . "...";
        /*$res = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.'0'.'}'.
                '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
            '$1',$str);
        $res .= '...';
        return $res;*/
    }

    /**
     * 获取字符串的长度，单位为字节，汉字为两个字节
     * @static
     * @param $str
     * @param int $len
     * @param string $char
     * @return int
     */
    public static function getLengthByByte($str)
    {
        $len = mb_strlen($str, 'UTF8');
        $i = 0;
        $l = 0;
        while ($i < $len) {
            $s = mb_substr($str, $i, 1, 'UTF8');
            $slen = strlen($s) == 3 ? 2 : strlen($s); //对汉字进行处理
            $l += $slen;
            $i++;
        }
        return $l;
    }

    /**
     *
     * @static
     * @param $str
     * @param string $flag(
     * 默认[空]:返回原字符串，
     * mid：返回中间，两头用*代替,
     * head_tail:返回字符串头部和尾部中间用****替代，
     * head:返回第一个后面用***代替，
     * tail:返回最后一个，前面用***代替)
     * @return string
     */
    public static function showStringCodeByFlag($str, $flag = '')
    {

        switch ($flag) {
            case  'head_tail':
                $str = $str[0] . "****" . $str[strlen($str) - 1];
                break;
            case 'head':
                $str = $str[0] . "***";
                break;
            case 'tail':
                $str = "***" . $str[strlen($str) - 1];
                break;
            case 'mid':
                $str = "**" . mb_substr(mb_substr($str, 1), -1) . "**";
            default:
                $str = $str;
        }
        return $str;
    }

    /**
     * 随机创建密码
     * @param type $pw_length
     * @return type
     */
    public static function createRandomStr($pw_length = 8, $numericOnly = false)
    {
        if ($numericOnly) {
            $chars = '012356789';
        } else
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12356789';
        //!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

        $randpwd = '';

        for ($i = 0; $i < $pw_length; $i++) {

            $randpwd .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $randpwd;
    }

    public static function utf8ToGb2312($utfStr)
    {
        return iconv("UTF-8", "GB2312//IGNORE", $utfStr);
    }

    /**
     * 获取指定长度的字符
     * @param $str
     * @param string $len
     * @param string $char
     * @return mixed
     */
    public static function setStrLength($str, $len = '', $char = "UTF8")
    {
        $strLen = strlen($str);
        if (empty($len) || $strLen == $len) {
            $res = $str;
        } else if ($strLen > $len) {
            $res = mb_substr($str, 0, $len,$char);
        } else {
            $res = $str;
            $count = $len - $strLen;
            for ($i = 0; $i < $count; $i++) {
                $res .= ' ';
            }
        }
        return $res;
    }

    public static function unzipJsonStr($zippedJson) {
        $objectStr = '';
        try{
            $objectStr  = gzuncompress($zippedJson);

            if(empty($objectStr)) {
                return null;
            }

            //解压缩后的字符串，会以 \u0001# 开头，需要先除掉多余字符：
            for($i = 0; $i < strlen($objectStr); $i++) {
                if ($objectStr[$i] == '{' || $objectStr[$i] == '[')
                    break;
            }
            $jsonStr = substr($objectStr, $i);

            if(empty($jsonStr) ) {
                return '';
            }

            return $jsonStr;

        }catch (Exception $ex){
            Yii::log("解压缩二进制对象时出错: 解压[$zippedJson]的结果[$objectStr] ，异常信息：" .
                $ex->getMessage(), 'error');
            return '';
        }

    }

    /**
     * 根据全路径拼接成的字符串获取每个全路径的根节点
     * 并将根节点以逗号连接为字符串后返回。
     * @static
     * @param $namePath Coverage 的namePath。
     * @return string
     */
    public static function getRootNameByNamePaths($namePaths,
                                                    $delimiter = ',', $pathDelimiter = '/'){
        $rootArray = array();

        $pathAry = explode($delimiter, $namePaths);

        foreach($pathAry as $pathItem){
            $rootItem = self::getFirstElement($pathItem, $pathDelimiter);
            if( strlen( trim($rootItem)) > 0 ){
                //确保重复的根节点，在结果中只出现一次：
                $rootArray[$rootItem] = $rootItem;
            }
        }

        if(count($rootArray) > 0)
            $result = implode(',', $rootArray);
        else
            $result = "";

        return $result;
    }
}
