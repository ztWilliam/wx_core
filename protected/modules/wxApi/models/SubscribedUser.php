<?php

/**
 * This is the model class for table "wxu_subscribed_user".
 *
 * The followings are the available columns in table 'wxu_subscribed_user':
 * @property integer $ghId
 * @property string $openId
 * @property string $firstSubscribedTime
 * @property string $lastSubscribedTime
 * @property string $lastUnSubscribedTime
 * @property integer $isSubscribed
 * @property integer $subscribedCount
 */
class SubscribedUser extends CActiveRecord
{
    /**
	 * Returns the static model of the specified AR class.
	 * @return SubscribedUser the static model class
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
		return 'wxu_subscribed_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, openId, firstSubscribedTime, lastSubscribedTime', 'required'),
			array('ghId, isSubscribed, subscribedCount', 'numerical', 'integerOnly'=>true),
            array('openId', 'length', 'max'=>64),
            array('firstSubscribedTime, lastSubscribedTime, lastUnSubscribedTime', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, openId, isSubscribed', 'safe', 'on'=>'search'),
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
			'openId' => 'Open Id',
			'firstSubscribedTime' => 'First Subscribed At',
			'lastSubscribedTime' => 'Last Subscribed At',
			'lastUnSubscribedTime' => 'Last Unsubscribed At',
			'isSubscribed' => 'Is Subscribed',
			'subscribedCount' => 'Subscribed Count',
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
		$criteria->compare('openId',$this->openId,true);
		$criteria->compare('isSubscribed',$this->isSubscribed,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    const IS_SUBSCRIBED_TRUE = 1;
    const IS_SUBSCRIBED_FALSE = 0;


    /**
     * 当用户关注时，更新关注的状态
     * @param $ghId
     * @param $openId
     * @throws Exception
     */
    public static function subscribe($ghId, $openId)
    {
        if(empty($ghId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'ghId', __METHOD__));
        }

        if(empty($openId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'openId', __METHOD__));
        }

        $obj = self::model()->findByPk(array('ghId' => $ghId, 'openId' => $openId));
        $now = date('Y-m-d H:i:s');
        if(!isset($obj)) {
            $obj = new SubscribedUser();
            $obj->ghId = $ghId;
            $obj->openId = $openId;
            $obj->subscribedCount = 0;
            $obj->firstSubscribedTime = $now;
        }

        $obj->lastSubscribedTime = $now;
        $obj->subscribedCount = $obj->subscribedCount + 1;
        $obj->isSubscribed = self::IS_SUBSCRIBED_TRUE;

        if(!$obj->save()){
            LogWriter::logModelSaveError($obj, __METHOD__, array(
                'ghId' => $ghId,
                'openId' => $openId,
            ));
        }
    }

    public static function unsubscribe($ghId, $openId)
    {
        if(empty($ghId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'ghId', __METHOD__));
        }

        if(empty($openId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'openId', __METHOD__));
        }

        $obj = self::model()->findByPk(array('ghId' => $ghId, 'openId' => $openId));
        $now = date('Y-m-d H:i:s');
        if(!isset($obj)) {
            //正常情况下肯定有记录，若无记录，说明该关注者是在本框架启用之前就已经关注了，所以只做参考性质的补记录
            $obj = new SubscribedUser();
            $obj->ghId = $ghId;
            $obj->openId = $openId;
            $obj->subscribedCount = 1;
            $obj->firstSubscribedTime = $now;
            $obj->lastSubscribedTime = $now;
        }

        $obj->lastUnSubscribedTime = $now;
        $obj->isSubscribed = self::IS_SUBSCRIBED_FALSE;

        if(!$obj->save()){
            LogWriter::logModelSaveError($obj, __METHOD__, array(
                'ghId' => $ghId,
                'openId' => $openId,
            ));
        }

    }


}