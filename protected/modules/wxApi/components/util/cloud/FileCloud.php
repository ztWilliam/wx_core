<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-12
 * Time: 下午3:07
 * To change this template use File | Settings | File Templates.
 */
//Yii::import('application.modules.wxApi.components.util.cloud.*');
require_once(dirname(__FILE__).'/qiniu/conf.php');
require_once(dirname(__FILE__).'/qiniu/http.php');
require_once(dirname(__FILE__).'/qiniu/io.php');
require_once(dirname(__FILE__).'/qiniu/rs.php');
require_once(dirname(__FILE__).'/qiniu/fop.php');
require_once(dirname(__FILE__).'/qiniu/rsf.php');
require_once(dirname(__FILE__).'/qiniu/resumable_io.php');

class FileCloud
{
    private $bucket;
    private $domain;
    private $bucketIsPrivate;

    public function initQiniuEnvironment($bucketName, $domain, $isPrivate = true){
        $this->bucket = $bucketName;
        $this->domain = $domain;
        $this->bucketIsPrivate = $isPrivate;
    }

    public function uploadFile($fileKey, $fileBody){

        $putPolicy = new Qiniu_RS_PutPolicy($this->bucket);
        $upToken = $putPolicy->Token(null);

        $putExtra = new Qiniu_PutExtra();
        $putExtra->Crc32 = 1;

        list($ret, $err) = Qiniu_Put($upToken, $fileKey, $fileBody, $putExtra);

        //$ret中包含 hash  和 key
        //$err
//        return array($ret, $err);
        if ($err !== null) {
            $this->logError($err, __METHOD__, array('fileKey' => $fileKey));
            return '';
        } else {
            $key = $ret['key'];
            return $this->fileUrl($key);
        }

    }

    public function getPrivateFile($fileKey){
        return $this->fileUrl($fileKey);
    }

    public function deleteFile($fileKey){
        $client = new Qiniu_MacHttpClient(null);
        Qiniu_RS_Delete($client, $this->bucket, $fileKey);
    }

    private function fileUrl($fileKey)
    {
        $baseUrl = Qiniu_RS_MakeBaseUrl($this->domain, $fileKey);

        if($this->bucketIsPrivate){
            $getPolicy = new Qiniu_RS_GetPolicy();
            $privateUrl = $getPolicy->MakeRequest($baseUrl, null);
            return $privateUrl;
        }
        else{
            return $baseUrl;
        }
    }

    private function logError($err, $methodName, $args = array())
    {

    }


}
