<?php

/**
 * This is the model class for table "wxa_qr_scene_image".
 *
 * The followings are the available columns in table 'wxa_qr_scene_image':
 * @property string $sceneInnerId
 * @property string $fileUrl
 * @property string $fileKey
 * @property integer $ticket
 */
class QrSceneImage extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return QrSceneImage the static model class
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
		return 'wxa_qr_scene_image';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('sceneInnerId, fileUrl, ticket', 'required'),
			array('sceneInnerId', 'length', 'max'=>20),
			array('fileUrl', 'length', 'max'=>500),
            array('fileKey', 'length', 'max'=>50),
			array('ticket', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('sceneInnerId, fileUrl, fileKey, ticket', 'safe', 'on'=>'search'),
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
			'sceneInnerId' => 'Scene Inner',
			'fileUrl' => 'File Url',
			'fileKey' => 'File Key',
			'ticket' => 'Ticket',
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

		$criteria->compare('sceneInnerId',$this->sceneInnerId,true);
		$criteria->compare('fileUrl',$this->fileUrl,true);
		$criteria->compare('fileKey',$this->fileKey,true);
		$criteria->compare('ticket',$this->ticket);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    const FILE_URL_NOT_SET = 'NOT SAVED';

    public function deleteFile(){
        if($this->fileKey !== self::FILE_URL_NOT_SET){
            $fileCloud = new FileCloud();
            $fileCloud->initQiniuEnvironment(FileCloudConfig::USER_PRIVATE_FILE_BUCKET,
                FileCloudConfig::FILE_SERVER_DOMAIN);
            $fileCloud->deleteFile($this->fileKey);
        }
    }

    public function getUrl(){
        if($this->fileKey == self::FILE_URL_NOT_SET){
            //根据ticket从微信服务器取url：
            return WxCgiCaller::getQrImageUrl($this->ticket);
        } else {
            $fileCloud = new FileCloud();
            $fileCloud->initQiniuEnvironment(FileCloudConfig::USER_PRIVATE_FILE_BUCKET,
                FileCloudConfig::FILE_SERVER_DOMAIN);

            return $fileCloud->getPrivateFile($this->fileKey);
        }
    }
}