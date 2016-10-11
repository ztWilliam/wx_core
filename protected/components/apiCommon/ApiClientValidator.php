<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-5-22
 * Time: 下午3:25
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.components.common.login.*');
class ApiClientValidator
{

    private static function allowedClients() {
        return array(
            //我有投顾 的微信应用业务api接口的访问token
            "51fc_WeChat" => array(
//                '115.28.12.249' => '1', //开发团队测试部署的服务器，正式发布时要去掉
                '115.29.149.69' => '1', //测试服务器的ip
                '127.0.0.1' => '1',
                'localhost' => '*'),
            //我有投顾 涉及到微信接口管理调用的api接口token
            "51fc_WxAdmin" => array(
                '115.29.149.69' => '1', //测试服务器的ip
                '127.0.0.1' => '1',
                'localhost' => '*'
            ),

            //MiniBank 涉及到微信接口管理调用的api接口token
            "mini_WxAdmin" => array(
                '115.29.149.69' => '1', //测试服务器的ip
                '127.0.0.1' => '1',
                'localhost' => '*'
            ),
        );
    }

    private static function existClient($clientToken, $clientIp){
        $clientList = self::allowedClients();

        $allowedIpList = $clientList[$clientToken];

        if(isset($allowedIpList) && is_array($allowedIpList) && array_key_exists($clientIp, $allowedIpList) ) {
            return true;
        } else {
            Yii::log('来自ip ' . $clientIp .' 的 ' . $clientToken . ' api访问。允许范围：' . FastJSON::encode($allowedIpList), 'warning');
            return false;
        }
    }

    public static function isValidClient($clientToken, $clientServerInfo) {
        $clientIp = HtmlFilter::remoteIpOfUser($clientServerInfo);

        return self::existClient($clientToken, $clientIp);
    }
}
