<?php

/**
 * This is the model class for table "wxa_gh_access_token".
 *
 * The followings are the available columns in table 'wxa_gh_access_token':
 * @property integer $ghId
 * @property string $appId
 * @property string $appSecret
 * @property string $accessToken
 * @property string $expireAt
 */
class GhAccessToken extends CActiveRecord
{
    const TOKEN_NOT_SET = 'none';

    //TOKEN失效的秒数，微信默认为7200秒，为了消除网络调用的时间损耗误差，将失效时间定为7000秒，确保在access token失效前，重新获取。
    const TOKEN_EXPIRES_IN_SECONDS = 7000;

    /**
	 * Returns the static model of the specified AR class.
	 * @return GhAccessToken the static model class
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
		return 'wxa_gh_access_token';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, accessToken, expireAt', 'required'),
			array('ghId', 'numerical', 'integerOnly'=>true),
            array('appId', 'length', 'max'=>20),
            array('appSecret', 'length', 'max'=>32),
            array('accessToken', 'length', 'max'=>512),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, appId, appSecret, accessToken, expireAt', 'safe', 'on'=>'search'),
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
			'ghId' => 'Gh',
            'appId' => 'App Id',
            'appSecret' => 'App Secret',
			'accessToken' => 'Access Token',
			'expireAt' => 'Expire At',
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

		$criteria->compare('ghId',$this->ghId);
        $criteria->compare('appId',$this->appId,true);
        $criteria->compare('appSecret',$this->appSecret,true);
		$criteria->compare('accessToken',$this->accessToken,true);
		$criteria->compare('expireAt',$this->expireAt,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function findAccessObj($ghId)
    {
        return self::model()->findByPk($ghId);
    }

    public static function getAccessToken($ghId)
    {
        $accessObj = self::findAccessObj($ghId);
        if(!isset($accessObj)) {
            throw new Exception('公众号的appId未保存');
        }

        //检查公众号下面的accessToken是否有效：
        if($accessObj->accessToken !== self::TOKEN_NOT_SET && $accessObj->expireAt >= date('Y-m-d H:i:s')) {
            return $accessObj->accessToken;
        }

        //若无效，重新申请一个，保存，并返回
        $newAccessToken = WxCgiCaller::getAccessToken($accessObj->appId, $accessObj->appSecret);
        if(empty($newAccessToken)){
            throw new WxException('无法获取新的AccessToken');
        }

        $accessObj->accessToken = $newAccessToken;
        $accessObj->expireAt = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + ' . self::TOKEN_EXPIRES_IN_SECONDS . ' second '));

        if(!$accessObj->save()){
            LogWriter::logModelSaveError($accessObj, __METHOD__, array(
                'new access token' => $newAccessToken,
                'ghId' => $ghId,
            ));
            throw new Exception('AccessToken更新失败');
        }

        return $newAccessToken;

    }


}