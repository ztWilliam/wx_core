<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-11-20
 * Time: 下午8:32
 * To change this template use File | Settings | File Templates.
 */

class WxConversationManager {

    public static function openConversation($params)
    {
        try{
            //必须项：
            $ghId = $params['ghId'];    //要开会话的公众号id（内部ID）
            $openId = $params['openId'];        //即将进入会话状态的openId
            $talkFor = $params['talkFor'];        //该会话的名称
            $answerHandler = $params['answerHandler'];      //会话过程中，处理用户发来的消息的handler
            $userLeftHandler = $params['userLeftHandler'];  //用户自行离开会话时的handler

            //非必须项：
            $desc = $params['desc'];        //会话的描述
            $expireMinutes = $params['expireMinutes'];  //超时关闭的时间
            $expiredHandler = $params['expiredHandler'];        //超时关闭后的handler
            $userLeavingHandler = $params['userLeavingHandler'];    //用户打算离开会话时的handler（主要用于给用户提示）

            // 存数据库
            $conversationObj = Conversation::createConversation($ghId, $openId, $talkFor,
                $answerHandler, $userLeftHandler, $desc, $expiredHandler, $expireMinutes, $userLeavingHandler);

            // 存入缓存：
            Conversation::addToCache($conversationObj);

            // 返回成功消息
            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array($conversationObj->id));

        }catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('开通会话状态失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '开通会话状态失败，请稍后重试', array()
            );
        }
    }

    public static function closeConversation($params)
    {
        try{
            $openId = $params['openId'];

            Conversation::closeConversation($openId);

            // 返回成功消息
            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        }catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('结束会话状态失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '结束会话状态失败，请稍后重试', array()
            );
        }
    }

    /**
     * 判断是否处在会话状态，
     * 如果在会话中，则返回会话对象
     * 如果不在会话中，则返回false
     *
     * @param $openId
     * @return array|bool
     */
    public static function isInConversation( $openId) {
        $obj = Conversation::inCache($openId);
        if($obj !== false){
            return $obj;
        }

        return false;
    }

    /**
     * 根据conversation的设置，回复客户发来的消息
     * 并更新会话状态（如：最近对话时间 等）
     * @param $openId
     * @param $conversation
     * @param $message
     */
    public static function answerMessage($openId, $conversation, $message, $parameters = "") {
        //更新最近会话时间：
        Conversation::updateLastTalkTime($openId, $conversation);

        if(Conversation::isInLeavingStatus($openId)){
            if(Conversation::confirmLeaving($conversation, $message)){
                return self::raiseUserLeftEvent($conversation, $message);
            }
        }

        return WxCommonFunction::callListenerHandler($conversation['answerHandler'],
            WxCommonDef::HANDLER_TYPE_URL, $message, $parameters);

    }

    /**
     * 提交一个用户可能要离开会话 的事件
     * 如果该会话有定义过UserLeavingHandler，则调用之；
     * 如果没有定义过，则使用默认的提示信息返回给用户：
     * “您即将离开[name]操作，回复大写字母 Y 确认离开，如需继续操作，可按之前提示内容回复消息即可。”
     *
     * @param $conversation
     * @param $userMessage
     * @return string
     */
    public static function raiseUserLeavingEvent($conversation, $userMessage){
        if($conversation['userLeavingHandler'] !== WxCommonDef::HANDLER_TYPE_NONE) {
            return WxCommonFunction::callListenerHandler($conversation['userLeavingHandler'],
                WxCommonDef::HANDLER_TYPE_URL, $userMessage);
        } else {
            //没有预定义的离开提醒，则给出系统提示的提醒：
            $leavingTip = Conversation::leavingTip($conversation);
            Conversation::addLeavingStatus($conversation);

            return WxCommonFunction::echoTextMessage($conversation['openId'], $userMessage->ToUserName, $leavingTip );
        }
    }

    /**
     * 只有在没有预设UserLeavingHandler的情况下，在默认提示信息下，用户回复了确认退出的消息的时候，才会发生本事件。

     * @param $conversation
     * @param $message
     * @return string
     */
    private static function raiseUserLeftEvent($conversation, $message){
        $userLeftHandler = $conversation['userLeftHandler'];
        //先关掉会话：
        Conversation::closeConversation($conversation['openId']);

        return WxCommonFunction::callListenerHandler($userLeftHandler, WxCommonDef::HANDLER_TYPE_URL, $message);
    }

    public static function raiseExpiredEvent($conversation){

    }


}