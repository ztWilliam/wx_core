<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-10
 * Time: 下午2:42
 * To change this template use File | Settings | File Templates.
 */

class WxGhManager {

    public static function registerGh($params, $url)
    {
        try{
            $ghId = $params['ghId'];
            $ghName = $params['ghName'];
            $ghDesc = $params['ghDesc'];
            $appId = $params['appId'];
            $appSecret = $params['appSecret'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的原始id');
            }
            if(empty($ghName)) {
                throw new WxException('请输入公众号的名称');
            }
            if(empty($appId)) {
                throw new WxException('请输入公众号的AppId');
            }
            if(empty($appSecret)) {
                throw new WxException('请输入公众号的AppSecret');
            }

            if(empty($url)) {
                throw new Exception('没有设置微信专用url');
            }

            $ghObj = GhDefinition::createNewGh($ghId, $ghName, $appId, $appSecret, $ghDesc, $url);

            $ghInfo = new WxGhInfo($ghObj->id, $ghObj->ghInitialId, $ghObj->token,
                $ghObj->getUrl(), $ghObj->getAppId(), $ghObj->getAppSecret(),
                $ghObj->ghName, $ghObj->ghDesc);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '', array($ghInfo));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('注册公众号信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '注册公众号信息失败，请稍后重试', array()
            );
        }
    }

    public static function getGhInfo($params, $url)
    {
        try{
            $id = $params['id'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }

            $ghObj = GhDefinition::getGhInfo($id, $url);

            $ghInfo = new WxGhInfo($ghObj->id, $ghObj->ghInitialId, $ghObj->token,
                $ghObj->getUrl(), $ghObj->getAppId(), $ghObj->getAppSecret(),
                $ghObj->ghName, $ghObj->ghDesc);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '', array($ghInfo));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取公众号信息失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取公众号信息失败，请稍后重试', array()
            );
        }

    }

    public static function resetAppSecret($params)
    {
        try{
            $id = $params['id'];
            $newAppSecret = $params['newAppSecret'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($newAppSecret)) {
                throw new WxException('请输入新的AppSecret');
            }

            GhDefinition::resetAppSecret($id, $newAppSecret);


            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('重置公众号AppSecret失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '重置公众号AppSecret失败，请稍后重试', array()
            );
        }

    }

    public static function getShortUrl($params)
    {
        try{
            $id = $params['id'];
            $longUrl = $params['longUrl'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($longUrl)) {
                throw new WxException('请输入要缩短的url');
            }


            $accessToken = GhAccessToken::getAccessToken($id);
            $shortUrl = WxCgiCaller::shortUrl($accessToken, $longUrl);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array($shortUrl));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取短链接失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取短链接失败，请稍后重试', array()
            );
        }

    }

    public static function getOAuthAccessToken($params)
    {
        try{
            $id = $params['id'];
            $code = $params['code'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }

            if(empty($code)) {
                throw new WxException('请输入页面code');
            }

            $oAuth = new WxOAuth($id);

            $info = $oAuth->getPageAccessToken($code);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '', array($info));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取页面授权accessToken失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取页面授权accessToken失败，请稍后重试', array()
            );
        }

    }

    public static function getOAuthUrl($params)
    {
        try{
            $id = $params['id'];
            $url = $params['url'];
            $scope = $params['scope'];

            if(empty($id)) {
                throw new WxException('请输入公众号的id');
            }

            if(empty($url)) {
                throw new WxException('请输入url');
            }

            if(is_null($scope)) {
                $scope = 'basic';
            }

            $oAuth = new WxOAuth($id);

            if($scope == 'basic') {
                $authUrl = $oAuth->baseScopeUrl($url);
            }else {
                $authUrl = $oAuth->userInfoScopeUrl($url);
            }

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '', array($authUrl));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取带oAuth功能url失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取带oAuth功能url失败，请稍后重试', array()
            );
        }
    }
}