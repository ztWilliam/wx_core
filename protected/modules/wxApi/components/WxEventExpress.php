<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-7
 * Time: 上午11:03
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.modules.wxApi.models.*');
class WxEventExpress {

    public static function processEvent($eventObj, $ghInfo) {

        //todo 由于微信每隔5秒重发，所以需要记录 fromUser ＋ createTime 以便排重。
        //检查是否是重发的消息，若是，则不做处理

        //（todo 第三次接到相同消息时，返回一个空字符串，）
        $result = "";

        //根据ghToken，获取公众号的原始Id
        $ghId = $ghInfo->id;
        $openId = $eventObj->FromUserName;

        //判断是否扫描事件
        if(isset($eventObj->Ticket)){
            //扫描二维码的事件

            // 更新activeUser状态
            ActiveUser::access($ghId, $openId);

            $result = QrScene::processScanEvent($ghInfo, $eventObj);

        } elseif($eventObj->Event == 'CLICK' || $eventObj->Event == 'VIEW') {
            //菜单点击事件

            // 更新activeUser状态
            ActiveUser::access($ghId, $openId);

            $result = GhMenu::processMenuEvent($ghInfo, $eventObj);
        } elseif($eventObj->Event == 'subscribe'){
            //关注事件 (指用户自主搜索关注的事件，并非扫描二维码进入)

            // 记录用户关注情况（便于以后数据分析）
            SubscribedUser::subscribe($ghId, $openId);

            // 更新activeUser状态
            ActiveUser::access($ghId, $openId);

            // 查找公众号预定义的消息接口：
            $handler = GhMessageHandler::findSubscribeHandler($ghInfo->id);
            if(isset($handler)){
                return WxCommonFunction::callEventHandler($handler->handler, WxCommonDef::HANDLER_TYPE_URL, $eventObj);
            } else {
                // 没有定义消息接口，则返回默认提示信息：
                throw new WxException('欢迎关注 ' . $ghInfo->ghName);
            }

        } elseif($eventObj->Event == 'unsubscribe'){
            //取消关注事件

            // 记录用户取消关注情况（便于以后数据分析）
            SubscribedUser::unsubscribe($ghId, $openId);

            // 查找公众号预定义的消息接口：
            $handler = GhMessageHandler::findUnSubscribeHandler($ghInfo->id);
            if(isset($handler)){
                //取消关注只是记录行为，无需返回信息（就算返回，用户也收不到了）
                WxCommonFunction::callEventHandler($handler->handler, WxCommonDef::HANDLER_TYPE_URL, $eventObj);
            }

        } elseif($eventObj->Event == 'LOCATION') {
            //上报地理位置事件

            // 更新activeUser状态
            ActiveUser::access($ghId, $openId);

            //todo 处理用户上报地理位置信息的接口调用：


        } elseif($eventObj->Event == 'TEMPLATESENDJOBFINISH') {
            //模板消息的发送结果反馈事件

            WxLogWriter::trace('TEMPLATESENDJOBFINISH event received: ' . FastJSON::encode($eventObj));
            // 调用TemplateMessage的processSendResult方法：
            WxTemplateManager::processTemplateSendJobFinish($ghInfo, $eventObj);

            //此类事件不需要返回响应内容，因此直接返回空字符串即可：
            return '';
        }

        //todo 将已处理的消息，从待处理事件中移除：（若与最近一次发送的时间已经超过10秒，则调用客服接口返回信息）


        return $result;

    }

}