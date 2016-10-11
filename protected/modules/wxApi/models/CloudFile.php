<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-12-12
 * Time: 下午11:07
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.modules.wxApi.components.*');
Yii::import('application.modules.wxApi.components.util.*');

/**
 * This is the model class for table "wxa_cloud_file".
 *
 * The followings are the available columns in table 'wxa_cloud_file':
 * @property integer $ghInnerId
 * @property string $fileId
 * @property string $mediaId
 * @property string $fileType
 * @property string $wxUrl
 * @property string $cloudFileKey
 * @property string $cloudFixedUrl
 * @property string $createdTime
 * @property string $cloudStatus
 */
class CloudFile extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return CloudFile the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'wxa_cloud_file';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ghInnerId, fileId, mediaId, createdTime', 'required'),
            array('ghInnerId, cloudStatus', 'numerical', 'integerOnly'=>true),
            array('fileId', 'length', 'max'=>32),
            array('fileType', 'length', 'max'=>20),
            array('cloudFileKey', 'length', 'max'=>50),
            array('wxUrl, cloudFixedUrl', 'length', 'max'=>500),
            array('mediaId', 'length', 'max'=>500),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('ghInnerId, fileId, cloudStatus, cloudFileKey, mediaId, wxUrl, cloudFixedUrl, createdTime', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'ghInnerId' => 'Gh ID',
            'fileId' => 'File Id',
            'fileType' => 'File Type',
            'mediaId' => 'Media ID',
            'wxUrl' => 'WeiXin Url',
            'cloudFileKey' => 'Cloud File Key',
            'cloudFixedUrl' => 'Cloud Fixed Url',
            'createdTime' => 'Created Time',
            'cloudStatus' => 'Cloud Status',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('ghInnerId',$this->ghInnerId);
        $criteria->compare('fileId',$this->fileId);
        $criteria->compare('fileType',$this->fileType);
        $criteria->compare('mediaId',$this->mediaId);
        $criteria->compare('wxUrl',$this->wxUrl);
        $criteria->compare('cloudFileKey',$this->cloudFileKey);
        $criteria->compare('cloudFixedUrl',$this->cloudFixedUrl);
        $criteria->compare('createdTime',$this->createdTime);
        $criteria->compare('cloudStatus',$this->cloudStatus);



        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    const CLOUD_STATUS_NOT_PROCESSED = 0;
    const CLOUD_STATUS_PROCESSING = 1;
    const CLOUD_STATUS_PROCESSED = 2;

    const URL_MAPPING_KEY_PREFIX_IN_CACHE = 'wx_file_';

    /**
     * 创建一个云文件对象
     *
     * @param $fileType
     * @param $MediaId
     * @param $ghInfo
     * @param $wxUrl
     * @return string
     * @throws Exception
     */
    public static function createFile($fileType, $MediaId, $ghInfo, $wxUrl)
    {
        $fileObj = new CloudFile();
        $fileObj->fileId = CommonFunction::create_guid();
        $fileObj->ghInnerId = $ghInfo->id;
        $fileObj->createdTime = date('Y-m-d H:i:s');
        $fileObj->fileType = $fileType;
        $fileObj->mediaId = $MediaId;
        $fileObj->wxUrl = $wxUrl;

        $fileObj->cloudFileKey = WxCommonDef::FIELD_NOT_DEFINED;
        $fileObj->cloudFixedUrl = WxCommonDef::FIELD_NOT_DEFINED;
        $fileObj->cloudStatus = self::CLOUD_STATUS_NOT_PROCESSED;


        if(!$fileObj->save()){
            LogWriter::logModelSaveError($fileObj, __METHOD__, array(
                'GhInnerId' => $ghInfo->id,
                'fileType' => $fileType,
                'mediaId' => $MediaId,
                'wxUrl' => $wxUrl,
            ));
            throw new Exception('CloudFile保存失败');
        }
        return $fileObj->fileId;

    }

    public static function urlOfFile($fileId, $ghId)
    {
        // 从cache中获取url，若有，则直接返回
        $cachingUrl = self::getUrlFromCache($fileId);
        if($cachingUrl !== ''){
            return $cachingUrl;
        }

        //从db中获取file对象
        $fileObj = self::model()->findByPk($fileId);
        if(!isset($fileObj)){
            return '';
        }

        if($fileObj->ghInnerId !==  $ghId) {
            throw new WxException('文件无权访问');
        }

        if($fileObj->cloudFixedUrl !== WxCommonDef::FIELD_NOT_DEFINED) {
            // 若db中的fixedUrl有效，则放到cache中，以便下次快捷获取
            self::addToCatch($fileObj);
        }

        $url = $fileObj->getFileUrl();

        return $url;
    }

    public function getFileUrl() {
        if($this->cloudStatus == self::CLOUD_STATUS_PROCESSED) {
            //已经上云的文件
            if($this->cloudFixedUrl == WxCommonDef::FIELD_NOT_DEFINED) {
                // 按私有云文件的方式取临时url：
                $fileCloud = new FileCloud();
                $fileCloud->initQiniuEnvironment(FileCloudConfig::USER_PRIVATE_FILE_BUCKET,
                    FileCloudConfig::FILE_SERVER_DOMAIN);

                return $fileCloud->getPrivateFile($this->cloudFileKey);

            } else {
                return $this->cloudFixedUrl;
            }
        } else {
            //还没上云的文件：
            if($this->wxUrl !== WxCommonDef::FIELD_NOT_DEFINED) {
                return $this->wxUrl;
            } else {

                return '';
            }
        }
    }

    public static function findUnprocessed(){
        $models = self::model()->findAllByAttributes(array('cloudStatus' => self::CLOUD_STATUS_NOT_PROCESSED),
            array('order' => 'createdTime asc'));

        return $models;
    }

    public function process($accessToken = ''){
        if(empty($accessToken)) {
            //除非外部没有传入accessToken，才自己取：
            //外部传了就直接用，可以减少数据库的访问。
            $accessToken = GhAccessToken::getAccessToken($this->ghInnerId);
        }

        $this->beginProcessing();

        List($fileKey, $fileUrl) = WxFileCarrier::transferFile($this->fileId, $this->mediaId, $accessToken);

        // 将状态变成已处理：
        $this->cloudFixedUrl = $fileUrl;
        $this->cloudFileKey = $fileKey;

        $this->cloudStatus = self::CLOUD_STATUS_PROCESSED;

        if(!$this->save()){
            LogWriter::logModelSaveError($this, __METHOD__, array(
                'fileId' => $this->fileId,
                'fileKey' => $this->cloudFileKey,
                'fixedUrl' => $this->cloudFixedUrl,
            ));
            throw new Exception(sprintf(WxException::ERR_MSG_MODEL_SAVE_ERROR, 'CloudFile', __METHOD__));
        }

        if($fileUrl !== WxCommonDef::FIELD_NOT_DEFINED) {
            // 将fileId => fixedUrl缓存：
            self::addToCatch($this);
        }

    }

    private function beginProcessing(){
        $this->cloudStatus = self::CLOUD_STATUS_PROCESSING;

        if(!$this->save()){
            LogWriter::logModelSaveError($this, __METHOD__, array(
                'fileId' => $this->fileId,
            ));
            throw new Exception(sprintf(WxException::ERR_MSG_MODEL_SAVE_ERROR, 'CloudFile', __METHOD__));
        }

    }

    public function cancelProcessing(){

    }

    public function toCachingObj(){
        $obj = array(
            'fileId' => $this->fileId,
            'fixedUrl' => $this->cloudFixedUrl,
            'fileType' => $this->fileType,
            'createdTime' => $this->createdTime,
        );

        return $obj;
    }

    private static function addToCatch($fileModel)
    {
        if(! $fileModel instanceof CloudFile) {
            throw new Exception(sprintf(WxException::ERR_MSG_TYPE_WRONG, 'CloudFile'));
        }

        $obj = $fileModel->toCachingObj();
        $key = self::URL_MAPPING_KEY_PREFIX_IN_CACHE . $fileModel->fileId;

        $cache = new RedisHelper();
        $cache->objectAdd($key, $obj);

    }

    private static function getUrlFromCache($fileId) {
        $key = self::URL_MAPPING_KEY_PREFIX_IN_CACHE . $fileId;

        $cache = new RedisHelper();
        $cachingModel = $cache->objectGet($key);

        if($cachingModel == false) {
            return '';
        }

        return $cachingModel['fixedUrl'];
    }


}