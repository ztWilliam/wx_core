<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 15-2-1
 * Time: 下午9:00
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.modules.wxApi.models.*');
class WxJsApiManager {

    public static function getJsApiSignPackage($params)
    {
        try {
            $ghId = $params['ghId'];
            $url = $params['url'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($url)) {
                throw new WxException('请输入目标页面的url');
            }

            $sighPackage = self::getSignPackage($ghId, $url);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($sighPackage));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取JsApi Package失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取JsApi Package失败，请稍后重试', array()
            );
        }
    }

    private static function getSignPackage($ghId, $url) {

        $ghInfo = GhDefinition::getGhInfo($ghId);

        $jsapiTicket = GhJsApiTicket::getTicket($ghId);

        $timestamp = time();
        $nonceStr = self::createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $ghInfo->getAppId(),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "jsApiList" => self::allJsApiList(),
        );

        return $signPackage;
    }

    private static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 返回所有目前版本（微信6.0）支持的js api
     * @return array
     */
    private static function allJsApiList()
    {
        return array(
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'startRecord',
            'stopRecord',
            'onVoiceRecordEnd',
            'playVoice',
            'pauseVoice',
            'stopVoice',
            'onVoicePlayEnd',
            'uploadVoice',
            'downloadVoice',
            'chooseImage',
            'previewImage',
            'uploadImage',
            'downloadImage',
            'translateVoice',
            'getNetworkType',
            'openLocation',
            'getLocation',
            'hideOptionMenu',
            'showOptionMenu',
            'hideMenuItems',
            'showMenuItems',
            'hideAllNonBaseMenuItem',
            'showAllNonBaseMenuItem',
            'closeWindow',
            'scanQRCode',
            'chooseWXPay',
            'openProductSpecificView',
            'addCard',
            'chooseCard',
            'openCard',
        );
    }

}