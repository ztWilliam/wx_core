<?php

/**
 * This is the model class for table "wxu_active_user".
 *
 * The followings are the available columns in table 'wxu_active_user':
 * @property integer $ghId
 * @property string $openId
 * @property string $lastAccessTime
 * @property integer $isOnline
 */
class ActiveUser extends CActiveRecord
{
    /**
	 * Returns the static model of the specified AR class.
	 * @return ActiveUser the static model class
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
		return 'wxu_active_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, openId, lastAccessTime', 'required'),
			array('ghId, isOnline', 'numerical', 'integerOnly'=>true),
            array('openId', 'length', 'max'=>64),
            array('lastAccessTime', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, openId, isOnline', 'safe', 'on'=>'search'),
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
			'lastAccessTime' => 'Last Access At',
			'isOnline' => 'Is Online',
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
		$criteria->compare('isOnline',$this->isOnline,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    const IS_ONLINE_TRUE = 1;
    const IS_ONLINE_FALSE = 0;
    const ONLINE_EXPIRE_IN = '48 hours';
    const ACTIVE_USER_SAVE_TERM = '1 months';

    /**
     *
     * @param $ghId
     * @param $openId
     * @throws Exception
     */
    public static function access($ghId, $openId)
    {
        if(empty($ghId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'ghId', __METHOD__));
        }

        if(empty($openId)) {
            throw new Exception(sprintf(WxException::ERR_MSG_PARAMETER_REQUIRED, 'openId', __METHOD__));
        }

        $obj = self::model()->findByPk(array('ghId' => $ghId, 'openId' => $openId));
        if(!isset($obj)) {
            $obj = new ActiveUser();
            $obj->ghId = $ghId;
            $obj->openId = $openId;
        }

        $obj->lastAccessTime = date('Y-m-d H:i:s');
        $obj->isOnline = self::IS_ONLINE_TRUE;

        if(!$obj->save()) {
            //本方法为辅助方法，因此如果操作失败，只记录日志，不抛出异常。
            LogWriter::logModelSaveError($obj, __METHOD__, array(
                'ghId' => $ghId,
                'openId' => $openId,
                'accessTime' => $obj->lastAccessTime,
            ));
        }

        //todo 将该记录放到redis中，以备后续查询：

    }

    public static function userIsActive($ghId, $openId) {
        //todo 看看redis里有没有，如果有，看看是否过期：
        //如果缓存中没有记录或者已过期（过期需要立刻删除缓存中记录），则到数据库中查询

        $obj = self::model()->findByPk(array('ghId' => $ghId, 'openId' => $openId));
        if(!isset($obj)) {
            return false;
        }

        if($obj->isOnline == self::IS_ONLINE_TRUE) {
            //todo 将该记录放到redis中，以备后续查询：

            return true;
        } else {
            return false;
        }
    }

    /**
     * 将超出online时限的活跃用户的online状态置为false
     */
    public static function clearOnlineExpired(){
        //计算出超时的时间
        $expireTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . self::ONLINE_EXPIRE_IN));

        //将 expireTime 之前的记录都置成 not online：
        self::model()->updateAll(array('isOnline' => self::IS_ONLINE_FALSE),
            " lastAccessTime <= '$expireTime' and isOnline = " . self::IS_ONLINE_TRUE );

    }

    /**
     * 将超出保存时限的“活跃用户”记录删除
     */
    public static function clearActiveExpired()
    {
        //计算出超时的时间
        $expireTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . self::ACTIVE_USER_SAVE_TERM));

        //将 expireTime 之前的记录都置成 not online：
        self::model()->deleteAll(array('isOnline' => self::IS_ONLINE_FALSE),
            " lastAccessTime < '$expireTime' " );
    }

}