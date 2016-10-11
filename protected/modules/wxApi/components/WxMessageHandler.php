<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-3-18
 * Time: 下午1:48
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.modules.wxApi.components.util.WxCommonFunction');
//define your token
class WxMessageHandler
{
    private function echoExceptionMessage($postObj, $message)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $msgType = 'text';
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";

        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $message);
        return $resultStr;
    }

    public function valid($parameters, $ghInfo)
    {
        $echoStr = $parameters["echostr"];
        $token = $ghInfo->token;

        //valid signature , option
        if($this->checkSignature($parameters, $token)){
            echo $echoStr;

            // 检测该公号是否有“验证成功”的事件处理，如果有，则调用之
            $handler = GhMessageHandler::findVerifiedHandler($ghInfo->id);
            if(isset($handler)){
                //由于是微信服务器发来的验证请求，所以没有传统意义上的 eventObj，
                //  因此自己构建一个，放置一些必要的信息
                $eventObj = array(
                    //微信官方服务器发过来的请求，所以不需要标示出FromUser：
                    'FromUserName' => WxCommonDef::FIELD_NOT_DEFINED,
                    //告诉消息接收方（第三方），是哪个公众号验证通过了
                    'ToUserName' => $ghInfo->id,
                );
                try {
                    //todo 优化方案：为防止第三方调用太耗时间，这里应该只在任务队列中添加任务，不做实际调用，由任务引擎负责空闲时调用。
                    //只需要调用，不需要返回任何信息
                    WxCommonFunction::callEventHandler($handler->handler, WxCommonDef::HANDLER_TYPE_URL, $eventObj);
                } catch (Exception $ex){
                    WxLogWriter::warning($ex->getMessage());
                }
            }

            exit;
        }
    }

    public function responseMsg($postStr, $ghInfo)
    {
        //get post data, May be due to the different environments
//        Yii::log('[WxApi] 待处理的post消息：'. $postStr , 'warning');

        if (!empty($postStr)){
            //将xml的post数据转换成对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        }else {
            //没有post数据，直接返回空，不处理消息：
            echo "";
            exit;
        }

        try{

            if(!isset($postObj))
                throw new WxException('没有对象！');

            if(!isset($ghInfo)) {
                throw new WxException('没有注册公号！');
            }

            $openId = $postObj->FromUserName;

//            Yii::log('接到微信消息 : ' . FastJSON::encode($postObj), 'warning');
            if($postObj->MsgType == "event"){
                // 判断是否在会话状态中，若有，则触发userLeaving事件
                $conversation = WxConversationManager::isInConversation($openId);
                if($conversation !== false){
                    // 更新activeUser状态
                    ActiveUser::access($ghInfo->id, $openId);

                    echo WxConversationManager::raiseUserLeavingEvent($conversation, $postObj);
                } else {
                    echo WxEventExpress::processEvent($postObj, $ghInfo);
                }

            } else {
                echo WxMessageExpress::processMessage($postObj, $ghInfo);
            }
            exit;
        } catch (WxException $ex) {
            // 构造显示错误提示信息的消息体：
            echo WxCommonFunction::echoTextMessage($postObj->FromUserName, $postObj->ToUserName, $ex->getMessage());
            exit;
        } catch (Exception $ex) {
            Yii::log('消息事件处理失败:' . $ex->getMessage(), 'error');

            echo WxCommonFunction::echoTextMessage($postObj->FromUserName, $postObj->ToUserName, "系统忙，请稍后重试");
//            echo $this->echoTextMessage($postObj, "系统忙，请稍后重试");
            exit;
        }
    }

    private function checkSignature($parameters, $token)
    {
        $signature = $parameters["signature"];
        $timestamp = $parameters["timestamp"];
        $nonce = $parameters["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}
