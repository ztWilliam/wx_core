<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-11
 * Time: 上午11:07
 * To change this template use File | Settings | File Templates.
 */
class WxQrManager
{

    public static function addLimitQrScene($params)
    {
        try{
            $ghId = $params['ghId'];
            $sceneId = $params['sceneId'];
            $handler = $params['handler'];
            $customParams = $params['params'];
            $desc = $params['desc'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($sceneId)) {
                throw new WxException('请输入永久二维码场景Id');
            }
            if(empty($handler)){
                throw new WxException('请输入该场景的业务处理类');
            }

            $qrScene = QrScene::createLimitQr($ghId, $sceneId,
                $handler, $customParams, $desc);

            $sceneInfo = new QrSceneInfo($qrScene->sceneId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($sceneInfo));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('新增永久二维码场景失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '新增永久二维码场景失败，请稍后重试', array()
            );
        }

    }

    public static function removeQrScene($params)
    {
        try {
            $ghId = $params['ghId'];
            $sceneId = $params['sceneId'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($sceneId)) {
                throw new WxException('请输入永久二维码场景Id');
            }

            QrScene::removeScene($ghId, $sceneId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('移除二维码场景失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '移除二维码场景失败，请稍后重试', array()
            );
        }
    }

    public static function addTempQrScene($params)
    {
        try{
            $ghId = $params['ghId'];
            $handler = $params['handler'];
            $customParams = $params['params'];
            $desc = $params['desc'];
            $expires = $params['expires'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($handler)){
                throw new WxException('请输入该场景的业务处理类');
            }

            if(empty($expires)) {
                $expires = QrScene::TEMP_QR_SCENE_EXPIRES_IN_SECONDS;
            }

            $qrScene = QrScene::createTempQr($ghId,
                $handler, $customParams, $desc, $expires);

            $sceneInfo = new QrSceneInfo($qrScene->sceneId, $qrScene->expire_seconds);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($sceneInfo));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('新增临时二维码场景失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '新增临时二维码场景失败，请稍后重试', array()
            );
        }

    }

    public static function getQrImageUrl($params)
    {
        try {
            $ghId = $params['ghId'];
            $sceneId = $params['sceneId'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的id');
            }
            if(empty($sceneId)) {
                throw new WxException('请输入二维码场景Id');
            }

            $url = QrScene::getQrImageUrl($ghId, $sceneId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($url));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取二维码图片失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取二维码图片失败，请稍后重试', array()
            );
        }

    }
}
