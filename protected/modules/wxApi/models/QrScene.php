<?php

Yii::import('application.modules.wxApi.components.*');
Yii::import('application.modules.wxApi.components.util.*');
Yii::import('application.modules.wxApi.components.util.cloud.*');

/**
 * This is the model class for table "wxa_qr_scene".
 *
 * The followings are the available columns in table 'wxa_qr_scene':
 * @property string $id
 * @property integer $ghId
 * @property integer $sceneId
 * @property string $classAlias
 * @property string $handlerType
 * @property string $parameters
 * @property integer $expire_seconds
 * @property string $createdTime
 * @property string $desc
 */
class QrScene extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return QrScene the static model class
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
		return 'wxa_qr_scene';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, sceneId, classAlias, handlerType, parameters, createdTime', 'required'),
			array('ghId, sceneId, expire_seconds', 'numerical', 'integerOnly'=>true),
			array('classAlias', 'length', 'max'=>200),
			array('handlerType', 'length', 'max'=>5),
			array('parameters', 'length', 'max'=>500),
			array('desc', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ghId, sceneId, classAlias, handlerType, parameters, expire_seconds, createdTime, desc', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'ghId' => 'Gh',
			'sceneId' => 'Scene',
			'classAlias' => 'Class Alias',
			'handlerType' => 'Handler Type',
			'parameters' => 'Parameters',
			'expire_seconds' => 'Expire Seconds',
			'createdTime' => 'Created Time',
			'desc' => 'Desc',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('ghId',$this->ghId,true);
		$criteria->compare('sceneId',$this->sceneId);
		$criteria->compare('classAlias',$this->classAlias,true);
		$criteria->compare('handlerType',$this->handlerType,true);
		$criteria->compare('parameters',$this->parameters,true);
		$criteria->compare('expire_seconds',$this->expire_seconds);
		$criteria->compare('createdTime',$this->createdTime,true);
		$criteria->compare('desc',$this->desc,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    private static function getSceneIdFromEventObject($eventObj)
    {

    }

    //永久二维码的场景Id，取值范围
    const LIMIT_QR_SCENE_MAX_ID = 100000;
    const LIMIT_QR_SCENE_MIN_ID = 1;

    //临时二维码的失效时间，微信默认1800秒
    const TEMP_QR_SCENE_EXPIRES_IN_SECONDS = 1800;

    public static function processScanEvent($ghInfo, $eventObj)
    {
        $event = $eventObj->Event;
        if($event == 'subscribe') {
            //用户是扫描并关注的情况：
            //scene id 是跟在“qrscene_”前缀之后的：
            $sceneId = TextUtility::getLastElement($eventObj->EventKey, '_');

        } elseif($event == 'SCAN') {
            //用户是已经关注，然后再扫描的情况
            $sceneId = $eventObj->EventKey;

        } else {
            Yii::log('处理扫描事件时发生错误：不可识别的事件类型：' .$event . ', 消息体：' . $eventObj , 'error');
            throw new WxException('不可识别的扫描事件');
        }

        $sceneObj = self::model()->findByAttributes(array('ghId' => $ghInfo->id, 'sceneId' => $sceneId));
        if(!isset($sceneObj)) {
            throw new WxException('二维码已失效');
        }
        if($sceneObj->isExpired()) {
            throw new WxException('二维码已失效');
        }

        //根据handlerType和classAlias的设定，调用相应的处理程序：
        $result = WxCommonFunction::callEventHandler($sceneObj->classAlias, $sceneObj->handlerType,
            $eventObj, $sceneObj->parameters);

        return $result;
//        $sceneId = self::getSceneIdFromEventObject($eventObj);

//        $sceneObj = self::model()->findByAttributes(array());

    }

    public static function createLimitQr($ghId, $sceneId, $handler, $customParams, $desc)
    {
        //检查sceneId 是否 1 -- 100000
        if($sceneId > self::LIMIT_QR_SCENE_MAX_ID ||
            $sceneId < self::LIMIT_QR_SCENE_MIN_ID) {
            throw new WxException('永久二维码的场景Id必须在' .
                self::LIMIT_QR_SCENE_MIN_ID . '--' .
                self::LIMIT_QR_SCENE_MAX_ID . '之间');
        }

        $handlerType = WxCommonFunction::checkHandler($handler);

        //检查sceneId是否重复
        if(self::existSceneId($ghId, $sceneId)) {
            throw new WxException('该场景Id已经使用');
        }

        $accessToken = GhAccessToken::getAccessToken($ghId);

        $ticket = WxCgiCaller::getQrLimitSceneTicket($accessToken, $sceneId);
        if(empty($ticket)) {
            throw new WxException('场景创建失败');
        }

        List($fileKey,$fileUrl) = self::saveImageFile($ticket);
        $expiresIn = ApiCommonDef::CONDITION_NO_LIMIT;

        $sceneObj = self::createNewSceneObj($ghId, $sceneId,
            $handler, $customParams, $desc, $expiresIn, $fileUrl, $fileKey, $ticket, $handlerType);

        return $sceneObj;

    }

    private static function createNewSceneObj($ghId, $sceneId, $handler,
                                               $customParams, $desc, $expiresIn, $fileUrl, $fileKey, $ticket, $handlerType)
    {
        $sceneObj = new QrScene();
        $sceneObj->ghId = $ghId;
        $sceneObj->sceneId = $sceneId;
        $sceneObj->classAlias = $handler;
        $sceneObj->handlerType = $handlerType;
        $sceneObj->parameters = $customParams;
        $sceneObj->desc = $desc;
        $sceneObj->createdTime = date('Y-m-d H:i:s');
        $sceneObj->expire_seconds = $expiresIn;

        if (!$sceneObj->save()) {
            LogWriter::logModelSaveError($sceneObj, __METHOD__, array(
                'ghId' => $ghId,
                'sceneId' => $sceneId,
                'classAlias' => $handler,
                'handlerType' => $handlerType,
                'params' => $sceneObj->parameters,
                'desc' => $desc,
            ));
            throw new Exception('场景保存失败');
        }

        $sceneImage = new QrSceneImage();
        $sceneImage->fileUrl = $fileUrl;
        $sceneImage->fileKey = $fileKey;
        $sceneImage->sceneInnerId = $sceneObj->id;
        $sceneImage->ticket = $ticket;
        if (!$sceneImage->save()) {
            LogWriter::logModelSaveError($sceneObj, __METHOD__, array(
                'fileUrl' => $fileUrl,
                'ticket' => $ticket,
            ));
            throw new Exception('场景图片保存失败');
        }
        return $sceneObj;
    }

    private static function saveImageFile($ticket)
    {
        // 根据$ticket获得二维码图片，并保存到云存储，得到文件的url
        $fileContent = WxCgiCaller::getQrImage($ticket);
        if(empty($fileContent)) {
           throw new WxException('二维码获取失败') ;
        }

        //上传文件服务器
        $fileCloud = new FileCloud();
        $fileCloud->initQiniuEnvironment(FileCloudConfig::USER_PRIVATE_FILE_BUCKET, FileCloudConfig::FILE_SERVER_DOMAIN);

        $fileKey = CommonFunction::create_guid() . '.jpg';
        $fileUrl = $fileCloud->uploadFile($fileKey, $fileContent);

        if(empty($fileUrl)) {
            throw new WxException('二维码图片文件保存失败');
        }

        return array($fileKey, $fileUrl);
    }

    private static function existSceneId($ghId, $sceneId)
    {
        $sceneObj = self::model()->findByAttributes(array('ghId' => $ghId,
            'sceneId' => $sceneId));

        if(isset($sceneObj)) {
            return true;
        } else {
            return false;
        }
    }

    public static function removeScene($ghId, $sceneId)
    {
        $sceneObj = self::model()->findByAttributes(array('ghId' => $ghId,
            'sceneId' => $sceneId));

        if(!isset($sceneObj)) {
            return true;
        }


        $sceneImage = QrSceneImage::model()->findByPk($sceneObj->id);
        if(isset($sceneImage)){
            if($sceneObj->expire_seconds == ApiCommonDef::CONDITION_NO_LIMIT){
                $sceneImage->deleteFile();
            }

            if(!$sceneImage->delete()){
                throw new Exception('删除二维码图片记录失败');
            }
        }

        if(!$sceneObj->delete()){
            throw new Exception('删除二维码场景记录失败');
        }

        return true;

    }

    public static function createTempQr($ghId, $handler, $customParams, $desc, $expires)
    {

        $sceneId = self::getTempSceneId($ghId);

        //检查handler是否有效
        $handlerType = WxCommonFunction::checkHandler($handler);

        $accessToken = GhAccessToken::getAccessToken($ghId);

        $ticket = WxCgiCaller::getQrTempSceneTicket($accessToken, $sceneId, $expires);
        if(empty($ticket)) {
            throw new WxException('场景创建失败');
        }

        if($expires > self::TEMP_QR_SCENE_EXPIRES_IN_SECONDS || $expires <= 0)
            $expiresIn = self::TEMP_QR_SCENE_EXPIRES_IN_SECONDS;
        else
            $expiresIn = $expires;

        $sceneObj = self::createNewSceneObj($ghId, $sceneId,
            $handler, $customParams, $desc, $expiresIn,
            QrSceneImage::FILE_URL_NOT_SET, QrSceneImage::FILE_URL_NOT_SET, $ticket, $handlerType);

        return $sceneObj;

    }

    private static function getTempSceneId($ghId, $tried = 0)
    {
        $microTimes = explode(" ", microtime());

        $milliseconds = explode('.' , $microTimes[0] * 100);

        $newSceneId = rand(1,9) . date('His') . $milliseconds[0];

        if(self::existSceneId($ghId, $newSceneId)){
            if($tried > 10) {
                //超过重试次数，则抛异常：
                throw new Exception('随机二维码id获取失败，超出重试次数');
            }
            //继续生成随机数：
            usleep(3000);  //延迟3毫秒
            return self::getTempSceneId($ghId, $tried + 1);
        } else {
            return $newSceneId;
        }

    }

    public static function getQrImageUrl($ghId, $sceneId)
    {
        $sceneObj = self::model()->findByAttributes(array('ghId' => $ghId,
            'sceneId' => $sceneId));

        if(!isset($sceneObj)){
            throw new WxException('二维码不存在或已失效');
        }

        if($sceneObj->isExpired()) {
            throw new WxException('二维码已失效');
        }
        $qrImage = QrSceneImage::model()->findByPk($sceneObj->id);
        if(!isset($qrImage)){
            throw new Exception('场景['.$sceneObj->id.']的图片记录不存在');
        }

        return $qrImage->getUrl();
    }

    public static function removeExpiredQrScene(){

        //todo 将已经超期的qr临时记录删除

    }

    public function isExpired() {
        if($this->expire_seconds == ApiCommonDef::CONDITION_NO_LIMIT){
            //永久二维码，永不过期：
            return false;
        }

        $expireTime = date('Y-m-d H:i:s',
            strtotime( $this->createdTime . ' + ' . $this->expire_seconds . ' second') );

        if($expireTime < date('Y-m-d H:i:s')){
            return true;
        } else {
            return false;
        }
    }
}