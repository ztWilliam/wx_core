<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-11
 * Time: 下午8:17
 * To change this template use File | Settings | File Templates.
 */

class HttpFunction {

//    public static function callHttp($url, $data=array(), $method='GET'){
//        $curl = curl_init(); // 启动一个CURL会话
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
//        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
//        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
//        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
//
//        if($method=='POST'){
//            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
//            if (is_array($data) && count($data) > 0){
//                curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
//            } elseif( isset($data) ) {
//                curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($data)); // Post提交json格式的数据包
//            }
//        } else {
//            //说明是get 方式，将参数自动添加到url中：
//            if (is_array($data) && count($data) > 0){
//                $url = $url.'?';
//                foreach($data as $field=>$value) {
//                    $url = $url . $field . '=' . $value. '&';
//                }
//
//                $url = trim($url, '&');
//            }
//        }
//
//        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
//        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
//        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
//        $tmpInfo = curl_exec($curl); // 执行操作
//        curl_close($curl); // 关闭CURL会话
//        return $tmpInfo; // 返回数据
//    }

    /**
     * 模拟提交url，支持https提交 可用于各类api请求
     * @param string $url ： 提交的地址（无需带参数）
     * @param array $data :参数数组 参数名 => 参数值
     * @param string $method : POST/GET，默认GET方式
     * @return mixed
     * @throws Exception
     */
    public static function callHttp($url, $data=array(), $method='GET'){
        $curl = new Curl();

        if($method == 'GET') {
            $tmpInfo = $curl->get($url, $data);
        } elseif ( $method == 'POST') {
            $tmpInfo = $curl->post($url, $data);
        } else {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'method[GET or POST]'));
        }

        WxLogWriter::warning(__METHOD__ . 'the result is ' . $tmpInfo);
        return $tmpInfo; // 返回数据
    }

}