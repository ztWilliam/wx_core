<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-9-24
 * Time: 下午2:46
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.modules.wxApi.models.*');
class WxUserManager
{

    public static function getUserInfo($params)
    {
        try{
            $id = $params['id'];
            $openId = $params['openId'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($openId)) {
                throw new WxException('请输入openId');
            }

            $accessToken = GhAccessToken::getAccessToken($id);
            $result = WxCgiCaller::getUserInfo($accessToken, $openId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($result));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取用户信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取用户信息失败，请稍后重试', array()
            );
        }

    }

    public static function clearExpiredOnlineUsers(){
        try{
            ActiveUser::clearOnlineExpired();

        } catch (Exception $ex) {
            Yii::log('清除过期的online user失败:' . $ex->getMessage(), 'error');
            throw $ex;
        }

    }

    public static function clearExpiredActiveUsers(){
        try{
            ActiveUser::clearActiveExpired();

        } catch (Exception $ex) {
            Yii::log('清除过期的active user失败:' . $ex->getMessage(), 'error');
            throw $ex;
        }

    }
}
