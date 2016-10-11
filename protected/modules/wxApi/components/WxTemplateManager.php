<?php
/**
 * 
 * User: william
 * Date: 15-3-29
 * Time: 下午7:54
 */

class WxTemplateManager {

    /**
     * 发送模板消息的接口
     *
     * @param $params
     * @return ApiResponseData
     */
    public static function sendTemplateMessage($params)
    {
        try{
            $ghId = $params['ghId'];
            $toUser = $params['toUser'];
            $content = trim($params['data']);
            $url = trim($params['url']);
            $templateId = trim($params['templateId']);
            //决定模板消息的topColor
            $topColor = $params['topColor'];

            $needOAuth = $params['oAuth'];
            if(empty($needOAuth)) {
                $needOAuth = 0;
            }

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($toUser)) {
                throw new WxException('请输入信息接受者的openid');
            }
            if(empty($templateId)){
                throw new WxException('请输入消息模板Id（可在微信后台查看）');
            }
            if($content == ''){
                throw new WxException('请输入要发送的消息内容');
            }

            $contentArray = FastJSON::decode($content);

            if($needOAuth > 0) {
                $oAuth = new WxOAuth($ghId);
                $url = $oAuth->baseScopeUrl($url);
            }

            // 调用Template Model 的发送方法，发送模板消息：
            $sendResult = Template::sendTemplateMessage($ghId, $toUser, $templateId, $url, $contentArray, $topColor);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '', array($sendResult));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('发送模板信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '发送模板信息失败，请稍后重试', array()
            );

        }

    }

    public static function registerTemplate($params)
    {
        try {
            // 获取参数：
            $ghId = $params['ghId'];
            $templateId = $params['templateId'];
            $successHandler = $params['successHandler'];
            $failedHandler = $params['failedHandler'];

            // 保存模板：
            $result = Template::saveTemplate($ghId, $templateId, $successHandler, $failedHandler);

            // 返回成功结果：
            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('注册消息模板失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '注册消息模板失败，请稍后重试', array()
            );
        }
    }

    public static function batchSendTemplateMessage($params)
    {
//        try{
//            //todo 获取参数
//
//            //todo 批量增加任务：
//
//            //todo 返回成功结果：
//            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
//                '', array());
//
//        } catch (WxException $ex) {
//            return new ApiResponseData(
//                ApiCommonDef::OPERATION_RESULT_FAILED,
//                $ex->getMessage(), array()
//            );
//        } catch (Exception $ex) {
//            Yii::log('批量发送模板信息失败:' . $ex->getMessage(), 'error');
//            return new ApiResponseData(
//                ApiCommonDef::OPERATION_RESULT_FAILED,
//                '批量发送模板信息失败，请稍后重试', array()
//            );
//
//        }

        //todo 下面的方法只是临时性地解决方案，待任务处理系统开发完成，需要改成向任务队列中增加任务

        try{
            $ghId = $params['ghId'];
            $toUsers = $params['toUsers'];
            $content = trim($params['data']);
            $url = trim($params['url']);
            $templateId = trim($params['templateId']);
            //决定模板消息的topColor
            $topColor = $params['topColor'];

            $needOAuth = $params['oAuth'];
            if(empty($needOAuth)) {
                $needOAuth = 0;
            }

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($toUsers)) {
                throw new WxException('请输入信息接受者的openid');
            }
            if(empty($templateId)){
                throw new WxException('请输入消息模板Id（可在微信后台查看）');
            }
            if($content == ''){
                throw new WxException('请输入要发送的消息内容');
            }

            $contentArray = FastJSON::decode($content);

            $toUserArray = FastJSON::decode($toUsers);

            if($needOAuth > 0) {
                $oAuth = new WxOAuth($ghId);
                $url = $oAuth->baseScopeUrl($url);
            }

            $sendResults = array();
            foreach($toUserArray as $toUser){
                if(!empty($toUser['params'])) {
                    //如果调用方，针对每个user有个性化的参数，则将此参数加入url中。
                    $url = sprintf($url, $toUser['params']);
                }
                // 调用Template Model 的发送方法，发送模板消息：
                $sendResults[] = Template::sendTemplateMessage($ghId, $toUser['openId'], $templateId, $url, $contentArray, $topColor);

            }

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '', array($sendResults));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('批量发送模板信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '批量发送模板信息失败，请稍后重试', array()
            );
        }
    }

    public static function processTemplateSendJobFinish($ghInfo, $eventObj)
    {
        // 处理成功或失败的发送结果：

        if($eventObj['Status'] == 'success') {
            // 发送成功的处理：
            Template::processMessageSucceed($ghInfo, $eventObj['MsgID']);

        } else {
            // 发送失败的处理：
            Template::processMessageFailed($ghInfo, $eventObj['MsgID'], $eventObj['Status']);
        }

    }
}