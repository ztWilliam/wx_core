<?php

/**
 * This is the model class for table "wxa_gh_definition".
 *
 * The followings are the available columns in table 'wxa_gh_definition':
 * @property integer $id
 * @property string $ghInitialId
 * @property string $token
 * @property string $uriPostfix
 * @property string $ghName
 * @property string $ghDesc
 * @property integer $delTag
 * @property string $createdTime
 */
class GhDefinition extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return GhDefinition the static model class
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
		return 'wxa_gh_definition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghInitialId, token, uriPostfix, ghName, createdTime', 'required'),
			array('delTag', 'numerical', 'integerOnly'=>true),
			array('ghInitialId', 'length', 'max'=>20),
			array('token', 'length', 'max'=>32),
			array('uriPostfix', 'length', 'max'=>8),
			array('ghName', 'length', 'max'=>50),
			array('ghDesc', 'length', 'max'=>140),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ghInitialId, token, uriPostfix, ghName, ghDesc, delTag, createdTime', 'safe', 'on'=>'search'),
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
			'ghInitialId' => 'Gh Initial',
			'token' => 'Token',
			'uriPostfix' => 'Uri Postfix',
			'ghName' => 'Gh Name',
			'ghDesc' => 'Gh Desc',
			'delTag' => 'Del Tag',
			'createdTime' => 'Created Time',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('ghInitialId',$this->ghInitialId,true);
		$criteria->compare('token',$this->token,true);
		$criteria->compare('uriPostfix',$this->uriPostfix,true);
		$criteria->compare('ghName',$this->ghName,true);
		$criteria->compare('ghDesc',$this->ghDesc,true);
		$criteria->compare('delTag',$this->delTag);
		$criteria->compare('createdTime',$this->createdTime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    private $accessObj;
    private $urlRoot;

    public function setUrlRoot($url) {
        $this->urlRoot = $url;
    }

    public function setAccessObj($ghAccessTokenObj){
        $this->accessObj = $ghAccessTokenObj;
    }

    public static function createNewGh($ghId, $ghName, $appId, $appSecret, $ghDesc, $url)
    {
        //检查ghId是否已经注册过：
        $ghObj = self::model()->findByAttributes(array('ghInitialId' => $ghId, 'delTag' => 0));
        if(isset($ghObj)) {
            $ghAccessObj = GhAccessToken::findAccessObj($ghObj->id);
            if($ghAccessObj->appSecret == $appSecret && $ghAccessObj->appId == $appId) {
                //判断一下已经注册的公众号的appId和appSecret是否与刚刚提交的相同，
                //若相同，则直接返回原来的对象即可：
                $ghObj->setUrlRoot($url);
                return $ghObj;
            }

            //如果没有通过appId和appSecret的检查，则报错：
            throw new WxException('公众号已经注册过');
        }

        $ghObj = new GhDefinition();
        $ghObj->ghInitialId = $ghId;
        $ghObj->ghName = $ghName;
        $ghObj->ghDesc = $ghDesc;
        $ghObj->createdTime = date('Y-m-d H:i:s');

        $ghAccessObj = new GhAccessToken();
        $ghAccessObj->appId = $appId;
        $ghAccessObj->appSecret = $appSecret;

        //分配token
        $ghObj->token = CommonFunction::create_guid();

        //生成uri后缀
        $ghObj->uriPostfix = UrlUtility::shrinkUrl($url, $ghObj->token);
        $ghObj->setUrlRoot($url);

        $trans = self::model()->getDbConnection()->beginTransaction();
        try{

            if(!$ghObj->save()) {
                LogWriter::logModelSaveError($ghObj, __METHOD__, array(
                    'ghId' => $ghId,
                    'appId' => $appId,
                    'appSecret' => $appSecret,
                    'token' => $ghObj->token,
                    'uri' => $ghObj->uriPostfix,
                    'ghName' => $ghName,
                    'ghDesc' => $ghDesc,
                ));

                throw new Exception('公众号信息保存失败。');
            }
            $ghAccessObj->ghId = $ghObj->id;
            $ghAccessObj->accessToken = GhAccessToken::TOKEN_NOT_SET;
            $ghAccessObj->expireAt = date('Y-m-d H:i:s');

            if(!$ghAccessObj->save()){
                LogWriter::logModelSaveError($ghAccessObj, __METHOD__, array(
                    'ghId' => $ghAccessObj->ghId,
                    'accessToken' => $ghAccessObj->accessToken,
                    'appId' => $ghAccessObj->appId,
                    'appSecret' => $ghAccessObj->appSecret,
                ));
                throw new Exception('AccessToken信息初始化失败');
            }
            $ghObj->setAccessObj($ghAccessObj);

            $trans->commit();

            return $ghObj;

        } catch (Exception $ex) {
            $trans->rollback();
            throw $ex;
        }


    }

    public static function getGhInfo($id, $url = "")
    {
        $ghObj = self::model()->findByPk($id);
        if(!isset($ghObj)){
            throw new WxException('公众号不存在');
        }

        $ghAccessObj = GhAccessToken::findAccessObj($ghObj->id);
        if(!isset($ghAccessObj)){
            throw new Exception('公众号'. $ghObj->id . ' 的AccessToken信息不存在');
        }

        $ghObj->setUrlRoot($url);
        $ghObj->setAccessObj($ghAccessObj);

        return $ghObj;
    }



    public function getUrl()
    {
        return $this->urlRoot . $this->uriPostfix . '/';
    }

    public function getAppId()
    {
        return $this->accessObj->appId;
    }

    public function getAppSecret()
    {
        return $this->accessObj->appSecret;
    }

    public static function resetAppSecret($id, $newAppSecret)
    {
        $ghObj = self::model()->findByPk($id);
        if(!isset($ghObj)){
            throw new WxException('公众号不存在');
        }

        $ghAccessObj = GhAccessToken::findAccessObj($ghObj->id);
        if(!isset($ghAccessObj)){
            throw new Exception('公众号'. $ghObj->id . ' 的AccessToken信息不存在');
        }

        $ghAccessObj->appSecret = $newAppSecret;
        $ghAccessObj->accessToken = GhAccessToken::TOKEN_NOT_SET;

        if(!$ghAccessObj->save()){
            LogWriter::logModelSaveError($ghAccessObj, __METHOD__, array(
                'ghId' => id,
                'ghName' => $ghObj->ghName,
                'newAppSecret' => $newAppSecret,
            ));
            throw new Exception('GhAccessToken信息，重置AppSecret时，保存失败');
        }
    }

    public static function getValidTokenByUri($key)
    {
        $ghObj = self::model()->findByAttributes(array('uriPostfix' => $key));
        if(!isset($ghObj)) {
            return '';
        }

        return $ghObj->token;
    }

    public static function findGhByUri($key) {
        $ghObj = self::model()->findByAttributes(array('uriPostfix' => $key));
        if(!isset($ghObj)) {
            return null;
        }

        return $ghObj;

    }


}