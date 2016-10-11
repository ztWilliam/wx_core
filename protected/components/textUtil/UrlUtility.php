<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 証拓
 * Date: 13-1-10
 * Time: 下午12:53
 * To change this template use File | Settings | File Templates.
 */
class UrlUtility
{
    /**
     * 根据Url生成短链接
     * @param $url
     * @return string
     */
    public static function getShortKeyByUrl($url)
    {
        $key = 'www.51fc.com.cn';
        $key .= time();
        return self::shrinkUrl($url, $key);


    }

    /**
     * 根据shortKey获取长链接
     * @param $shortKey
     * @return string
     */
    public static function getUrlByShortKey($shortKey)
    {
        $pre_link = ShortLinkHelper::getHost();
        return UrlUtility::addHttpToUrl(trim($pre_link, '/')) . "/" . $shortKey;
    }

    public static function getMobileUrlByShortKey($shortKey) {
        $pre_link = ShortLinkHelper::getMobileHost();
        return UrlUtility::addHttpToUrl(trim($pre_link, '/')) . "/" . $shortKey;
    }

    /**
     * 根据访问用户IP判断所属区域信息
     * @param $ip
     * @return string
     */
    public static function getIpInfoByIp($ip = '')
    {

        if (empty($ip)) {
            //获取用户端ip
            if (isset($_SERVER)) {
                if (isset($_SERVER[HTTP_X_FORWARDED_FOR])) {
                    $ip = $_SERVER[HTTP_X_FORWARDED_FOR];
                } elseif (isset($_SERVER[HTTP_CLIENT_IP])) {
                    $ip = $_SERVER[HTTP_CLIENT_IP];
                } else {
                    $ip = $_SERVER[REMOTE_ADDR];
                }
            } else {
                if (getenv("HTTP_X_FORWARDED_FOR")) {
                    $ip = getenv("HTTP_X_FORWARDED_FOR");
                } elseif (getenv("HTTP_CLIENT_IP")) {
                    $ip = getenv("HTTP_CLIENT_IP");
                } else {
                    $ip = getenv("REMOTE_ADDR");
                }
            }
        }

        $sUrl = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";
        $tUrl = "http://ip.taobao.com/service/getIpInfo.php?ip=$ip";

        $res = json_decode(file_get_contents($sUrl)); // 利用新浪接口根据ip查询所在区域信息
        if (empty($res)) {
            $res = json_decode(file_get_contents($tUrl)); // 利用淘宝接口根据ip查询所在区域信息
            if (empty($res)) {
                return array('code' => 1, 'desc' => '无效的IP:' . $ip);
            }
        }
        return $res;
    }

    /**
     * 检测url前有没有 http:// ,没有的话给加上
     * @param $url
     * @return string
     */
    public static function addHttpToUrl($url)
    {
        if (strtolower(mb_substr($url, 0, 7)) == 'http://') {
            return $url;
        }
        return "http://" . $url;
    }

    /**
     * 将$url混合某个key值，缩成6位唯一字符串。
     * 此方法可用于生成短链接
     *
     * @param $url
     * @param $key
     * @return mixed
     */
    public static function shrinkUrl($url, $key)
    {
        $base32 = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZaBcDeFgHiJkLmNoPqRsTuVwXyZ";

        // 利用md5算法方式生成hash值
        $hex = hash('md5', $url . $key);
        $hexLen = strlen($hex);
        $subHexLen = $hexLen / 8;

        $output = array();
        for ($i = 0; $i < $subHexLen; $i++) {
            // 将这32位分成四份，每一份8个字符，将其视作16进制串与0x3fffffff(30位1)与操作
            $subHex = substr($hex, $i * 8, 8);
            $idx = 0x3FFFFFFF & (1 * ('0x' . $subHex));
            // 这30位分成6段, 每5个一组，算出其整数值，然后映射到我们准备的62个字符
            $out = '';
            for ($j = 0; $j < 6; $j++) {
                $val = 0x0000003D & $idx;
                $out .= $base32[$val];
                $idx = $idx >> 5;
            }
            $output[$i] = $out;
        }

        return $output[0];
    }

}
