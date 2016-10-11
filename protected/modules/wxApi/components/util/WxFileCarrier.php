<?php
/**
 * 负责文件搬运工作
 * 把 CloudFile中记录的没有放到云存储上的文件，从微信服务器上下载下来，
 * 转存到云端。
 *
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-12-18
 * Time: 上午10:36
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.modules.wxApi.components.util.cloud.*');

class WxFileCarrier {

    public static function transferFile($fileId, $mediaId, $accessToken)
    {
        // 根据mediaId下载文件
        $fileData = WxCgiCaller::downloadFile($accessToken, $mediaId);

        // 上传cloud
        $fileKey = $fileId;
        $fileCloud = new FileCloud();

        //todo 目前七牛只有一个私有空间，待升级七牛之后，设立公有空间，即可获取永久有效的url
        //todo 待有公共空间后，需要修改七牛的环境参数：（现在暂时都放在私有空间里）
        $fileCloud->initQiniuEnvironment(FileCloudConfig::USER_PRIVATE_FILE_BUCKET, FileCloudConfig::FILE_SERVER_DOMAIN);

        $fileUrl = $fileCloud->uploadFile($fileKey, $fileData);

        if(empty($fileUrl)) {
            throw new WxException('文件迁移失败，CloudFile Id:' . $fileId);
        }

        //todo 将来有公共空间后，需要赋值为七牛返回的url：
        $fileFixedUrl = WxCommonDef::FIELD_NOT_DEFINED;

        return array($fileKey, $fileFixedUrl);

    }
}