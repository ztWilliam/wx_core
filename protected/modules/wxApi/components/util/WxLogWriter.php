<?php
/**
 * 
 * User: william
 * Date: 15-3-31
 * Time: 上午11:59
 */

class WxLogWriter {

    public static function trace($logContent){
        if(WxCommonDef::DEBUG_MODE){
            Yii::log('[WxApi Tracing]' . $logContent, 'trace');
        }
    }

    public static function warning($logContent) {
        Yii::log('[WxApi Warning]' . $logContent, 'warning');
    }

    public static function error($logContent) {
        Yii::log('[WxApi Error]' . $logContent, 'error');
    }
}