<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-7
 * Time: 上午11:03
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.modules.wxApi.models.*');
Yii::import('application.modules.wxApi.components.util.*');
class WxMessageExpress {
    public static function processMessage($msgObj, $ghInfo) {

        $fromUsername = $msgObj->FromUserName;

        // 更新activeUser状态
        ActiveUser::access($ghInfo->id, $fromUsername);

        //判断消息体中有没有文件，若有文件，则在本地暂存，生成统一的文件id：
        $fileId = WxFileManager::extractFileFromMessage($msgObj, $ghInfo);
        if($fileId !== '') {
            $parameters = array(
                WxFileManager::FILE_GUID_KEY => $fileId,
            );
        }

        //需要额外传递的参数若不为空，则转换为Json，传递给handler
        $paramStr = empty($parameters) ? "" : FastJSON::encode($parameters);

        // 判断是否在会话状态中，若在会话中，则调用会话的处理接口：
        $conversation = WxConversationManager::isInConversation($fromUsername);
        if($conversation !== false){
            return WxConversationManager::answerMessage($fromUsername, $conversation, $msgObj, $paramStr);
        } else {
            // 查找公众号预定义的消息接口：
            $handler = GhMessageHandler::findMessageHandler($ghInfo->id);
            if(isset($handler)){
                return WxCommonFunction::callListenerHandler($handler->handler, WxCommonDef::HANDLER_TYPE_URL, $msgObj, $paramStr);
            } else {
                // 如果没有定义消息接口，则返回提示信息：
                throw new WxException('该公众号尚未设置消息处理接口，您所发的消息将不会送达公众号所有者');
            }
        }
    }

    public static function sendCustomTextMessage($params){
        try{
            $id = $params['ghId'];
            $toUser = $params['toUser'];
            $content = trim($params['content']);

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($toUser)) {
                throw new WxException('请输入信息接受者的openid');
            }
            if($content == ''){
                throw new WxException('请输入要发送的消息内容');
            }

            $accessToken = GhAccessToken::getAccessToken($id);

            $sendContent = WxJsonTemplate::customTextMessage($toUser, $content);

            $result = WxCgiCaller::sendCustomMessage($accessToken, $sendContent);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '', array($result));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('发送客服信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '发送客服信息失败，请稍后重试', array()
            );
        }

    }

    public static function setGhMessageHandler($params)
    {
        try{
            $ghId = $params['ghId'];
            $handler = $params['handler'];

            $ghInfo = GhDefinition::getGhInfo($ghId, '');
            $ghInitialId = $ghInfo->ghInitialId;

            $handlerObj = GhMessageHandler::saveMessageHandler($ghId, $ghInitialId, $handler);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log(__METHOD__ . ': 设置消息处理接口失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '设置消息处理接口失败，请稍后重试', array()
            );
        }
    }

    public static function setSubscribeHandler($params)
    {
        try{
            $ghId = $params['ghId'];
            $handler = $params['handler'];

            $ghInfo = GhDefinition::getGhInfo($ghId, '');
            $ghInitialId = $ghInfo->ghInitialId;

            $handlerObj = GhMessageHandler::saveSubscribeHandler($ghId, $ghInitialId, $handler);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log(__METHOD__ . ': 设置关注处理接口失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '设置关注处理接口失败，请稍后重试', array()
            );
        }
    }

    public static function setUnSubscribeHandler($params)
    {
        try{
            $ghId = $params['ghId'];
            $handler = $params['handler'];

            $ghInfo = GhDefinition::getGhInfo($ghId, '');
            $ghInitialId = $ghInfo->ghInitialId;

            $handlerObj = GhMessageHandler::saveUnSubscribeHandler($ghId, $ghInitialId, $handler);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log(__METHOD__ . ': 设置取消关注处理接口失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '设置取消关注处理接口失败，请稍后重试', array()
            );
        }
    }

    public static function setUrlVerifiedHandler($params)
    {
        try{
            $ghId = $params['ghId'];
            $handler = $params['handler'];

            $ghInfo = GhDefinition::getGhInfo($ghId, '');
            $ghInitialId = $ghInfo->ghInitialId;

            $handlerObj = GhMessageHandler::saveUrlVerifiedHandler($ghId, $ghInitialId, $handler);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log(__METHOD__ . ': 设置url验证处理接口失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '设置url验证处理接口失败，请稍后重试', array()
            );
        }
    }
}