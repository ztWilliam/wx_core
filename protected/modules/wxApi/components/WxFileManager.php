<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-12-12
 * Time: 下午11:05
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.modules.wxApi.models.CloudFile');
class WxFileManager {

    const FILE_GUID_KEY = 'FileGUID';

    //目前能支持的微信多媒体文件类型
    //因为微信的下载接口，不支持视频文件下载，所以暂时不能提供从微信消息中提取
    private static function fileTypes() {
        return array(
            WxCommonDef::FILE_TYPE_IMAGE => 'image files',
            WxCommonDef::FILE_TYPE_VOICE => 'audio files',
        );
    }

    public static function extractFileFromMessage($msgObj, $ghInfo)
    {
        $msgType = trim($msgObj->MsgType);
        $fileTypes = self::fileTypes();
        if (!array_key_exists($msgType, $fileTypes)) {
            //不是支持的文件类型，则直接返回空值
            return '';
        }

        if($msgType == WxCommonDef::FILE_TYPE_IMAGE) {
            $wxUrl = $msgObj->PicUrl;
        } else {
            $wxUrl = WxCommonDef::FIELD_NOT_DEFINED;
        }

        $fileId = CloudFile::createFile($msgType, $msgObj->MediaId, $ghInfo, $wxUrl);

        return $fileId;
    }

    public static function getFileUrl($params)
    {
        try {
            $ghId = $params['ghId'];
            $fileId = $params['fileId'];

            if(empty($ghId)) {
                throw new WxException('请输入公众号的原始id');
            }
            if(empty($fileId)) {
                throw new WxException('请输入文件ID');
            }

            $url = CloudFile::urlOfFile($fileId, $ghId);

            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功', array($url));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('获取文件url失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '获取文件url失败，请稍后重试', array()
            );
        }
    }

    public static function  transferFileToCloud($maxHours = 0){
        try {
            $unprocessedFiles = CloudFile::findUnprocessed();

            //todo 记录开始时间
            $beginTime = date('Y-m-d H:i:s');

            foreach($unprocessedFiles as $wFile) {
                try {
                    $wFile->process();

                    //todo 判断是否到$maxHours，若到时，还没处理的文件就不处理了


                } catch (Exception $ex) {
                    Yii::log();
                    //确保该文件重新被置成“未处理状态”
                    $wFile->cancelProcessing();
                }
            }

            //todo 输出信息: 几点开始，几点结束，一共需要转存多少，实际转存了多少文件：


        }catch (WxException $ex) {
            throw new Exception('将文件从微信服务器传上云存储时，中止操作:' . $ex->getMessage());
        } catch (Exception $ex) {
            Yii::log('将文件从微信服务器传上云存储时，失败:' . $ex->getMessage(), 'error');
            throw new Exception('将文件从微信服务器传上云存储时，失败:' . $ex->getMessage());
        }
    }
}