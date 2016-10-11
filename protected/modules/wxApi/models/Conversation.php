<?php


/**
 * This is the model class for table "wxa_conversation".
 *
 * The followings are the available columns in table 'wxa_conversation':
 * @property integer $id
 * @property integer $ghId
 * @property integer $expireMinutes
 * @property string $openId
 * @property string $talkFor
 * @property string $answerHandler
 * @property string $userLeftHandler
 * @property string $expiredHandler
 * @property string $userLeavingHandler
 * @property string $desc
 * @property integer $isClosed
 * @property string $createdTime
 */
class Conversation extends CActiveRecord
{
    //redis中的key前缀
    const CONVERSATION_KEY_PREFIX = 'wx_talk_';
    //会话失效的分钟数，默认为600分钟（10小时）。
    const CONVERSATION_EXPIRES_DEFAULT_MINUTES = 600;
    //会话失效的最大分钟数，当传入参数大于此值时，直接取这个值（24小时）
    const CONVERSATION_EXPIRES_MAX_MINUTES = 1440;

    //用户拟离开会话时的提醒：
    const CONVERSATION_LEAVING_CONFIRM_KEYWORD = '退出';
    const CONVERSATION_LEAVING_TIP_TEMPLATE = '您正在进行“%s”，要离开吗？回复括号内的文字(%s)，退出当前操作。';
    const CONVERSATION_LEAVING_STATUS_KEY_PREFIX = 'wx_leaving_talk_';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className
     * @return CActiveRecord the static model class
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
		return 'wxa_conversation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, openId, talkFor, answerHandler, userLeftHandler', 'required'),
			array('ghId, expireMinutes, isClosed', 'numerical', 'integerOnly'=>true),
            array('openId', 'length', 'max'=>64),
            array('talkFor', 'length', 'max'=>30),
            array('answerHandler, userLeftHandler, expiredHandler, userLeavingHandler', 'length', 'max'=>100),
            array('desc', 'length', 'max'=>140),
            array('createdTime', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, openId, talkFor, isClosed, createdTime', 'safe', 'on'=>'search'),
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
            'talkFor' => 'Talk For',
			'answerHandler' => 'Answer Handler',
			'userLeftHandler' => 'User Left Handler',
            'isClosed' => 'Closed',
            'desc' => 'Description',
            'expireMinutes' => 'Expire in minutes',
            'expiredHandler' => 'Expired Handler',
            'userLeavingHandler' => 'User Leaving Handler',
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
        $criteria->compare('isClosed',$this->isClosed,true);
		$criteria->compare('talkFor',$this->talkFor,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * 判断$openId是否在会话状态？
     * 若在，则返回会话对象，若不在，则返回false
     *
     * @param $openId
     * @return bool|object
     */
    private static function inConversation($openId)
    {
        // 先检查是否在cache中
        $cachedObj = self::inCache($openId);
        if($cachedObj !== false) {
            return $cachedObj;
        }

        // 检查数据库中是否存在isClosed是false的记录：
        $persistentObj = self::findActiveConversation($openId);
        if(isset($persistentObj)){
            return self::toJsonObj($persistentObj);
        }

        //若都没找到，则返回false
        return false;
    }

    public static function createConversation($ghId, $openId, $talkFor,
                                              $answerHandler, $userLeftHandler, $desc, $expiredHandler,
                                              $expireMinutes, $userLeavingHandler)
    {
        // 检查必填项：
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($openId)){
            throw new WxException('请输入参与会话的用户openId');
        }
        if(empty($talkFor)){
            throw new WxException('请输入会话主题');
        }
        if(empty($answerHandler)){
            throw new WxException('请输入会话处理回调接口');
        }
        if(empty($userLeftHandler)) {
            throw new WxException('请输入用户离开会话时的回调接口');
        }
        if(empty($expireMinutes)){
            $expireMinutes = self::CONVERSATION_EXPIRES_DEFAULT_MINUTES;
        }

        // 检查数据范围取值：
        if(mb_strlen($talkFor, 'utf8') > 30) {
            throw new WxException('会话主题不能超过30字符');
        }
        if(mb_strlen($answerHandler, 'utf8') > 100 ||
            mb_strlen($userLeftHandler, 'utf8') > 100 ||
            mb_strlen($userLeavingHandler, 'utf8') > 100 ||
            mb_strlen($expiredHandler, 'utf8') > 100 ) {
            throw new WxException('回调接口不能超过100字符');
        }
        if($expireMinutes > self::CONVERSATION_EXPIRES_MAX_MINUTES) {
            $expireMinutes = self::CONVERSATION_EXPIRES_MAX_MINUTES;
        }
        //如果非必填的handler是空值，则赋值为none：
        if(empty($expiredHandler)) {
            $expiredHandler = WxCommonDef::HANDLER_TYPE_NONE;
        }
        if(empty($userLeavingHandler)){
            $userLeavingHandler = WxCommonDef::HANDLER_TYPE_NONE;
        }

        // 检查该用户当前是否正在会话状态：
        $conversation = self::inConversation($openId);
        if($conversation !== false){
            throw new WxException('当前用户正在“' . $conversation['talkFor'] . '”会话状态中，请先退出当前会话');
        }

        // 存数据库：
        $obj = new Conversation();
        $obj->ghId = $ghId;
        $obj->openId = $openId;
        $obj->talkFor = $talkFor;
        $obj->answerHandler = $answerHandler;
        $obj->userLeftHandler = $userLeftHandler;
        $obj->userLeavingHandler = $userLeavingHandler;
        $obj->expiredHandler = $expiredHandler;
        $obj->expireMinutes = $expireMinutes;
        $obj->desc = $desc;
        $obj->isClosed = 0;
        $obj->createdTime = date('Y-m-d H:i:s');

        if(!$obj->save()){
            LogWriter::logModelSaveError($obj, __METHOD__, array(
                'ghId' => $obj->ghId,
                'openId' => $obj->openId,
                'talkFor' => $obj->talkFor,
                'answerHandler' => $obj->answerHandler,
                'userLeftHandler' => $obj->userLeftHandler,
                'userLeavingHandler' => $obj->userLeavingHandler,
                'expiredHandler' => $obj->expiredHandler,
                'expireMinutes' => $obj->expireMinutes,
            ));
            throw new Exception('Conversation对象保存失败');
        }

        return $obj;
    }


    public static function inCache($openId)
    {
        $key = self::CONVERSATION_KEY_PREFIX . $openId;
        $cache = new RedisHelper();
        $obj = $cache->objectGet($key);

        if(!isset($obj) || $obj == false) {
            return false;
        }
        return $obj;
    }

    public static function addToCache($conversationObj)
    {
        $obj = self::toJsonObj($conversationObj);

        $key = self::CONVERSATION_KEY_PREFIX . $conversationObj->openId;
        $cache = new RedisHelper();
        $cache->objectAdd($key, $obj);
    }

    public static function updateLastTalkTime($openId, $conversation)
    {
        $conversation['lastTalkTime'] = date('Y-m-d H:i:s');

        $key = self::CONVERSATION_KEY_PREFIX . $openId;
        $cache = new RedisHelper();
        $cache->objectAdd($key, $conversation);

    }


    public static function closeConversation($openId)
    {
        if(empty($openId)) {
            throw new WxException('请输入会话者的openId');
        }

        // 数据库中标记closed：
        self::setCloseTag($openId);

        // 从cache中移除
        $key = self::CONVERSATION_KEY_PREFIX . $openId;
        $cache = new RedisHelper();
        $cache->objectRemove($key);

    }

    private static function findActiveConversation($openId)
    {
        $obj = self::model()->findByAttributes(array('openId' => $openId, 'isClosed' => 0));

        return $obj;
    }

    private static function toJsonObj($obj)
    {
        if(isset($obj) && ($obj instanceof Conversation)) {
            return array(
                'id' => $obj->id,
                'ghId' => $obj->ghId,
                'openId' => $obj->openId,
                'talkFor' => $obj->talkFor,
                'answerHandler' => $obj->answerHandler,
                'userLeftHandler' => $obj->userLeftHandler,
                'userLeavingHandler' => $obj->userLeavingHandler,
                'expiredHandler' => $obj->expiredHandler,
                'expireMinutes' => $obj->expireMinutes,
                'createTime' => $obj->createdTime,
                'lastTalkTime' => '',
            );
        } else {
            throw new Exception(__METHOD__ . '：传入对象不合法。');
        }
    }

    private static function setCloseTag($openId)
    {
        $obj = self::findActiveConversation($openId);
        if(isset($obj)){
            $obj->isClosed = 1;
            if(!$obj->save()){
                LogWriter::logModelSaveError($obj, __METHOD__, array(
                    'openId' => $openId,

                ));
                throw new Exception('未能关闭会话');
            }
        }
    }

    public static function leavingTip($conversation)
    {
        $tip = sprintf(self::CONVERSATION_LEAVING_TIP_TEMPLATE,
            $conversation['talkFor'],
            self::CONVERSATION_LEAVING_CONFIRM_KEYWORD);

        return $tip;
    }

    public static function addLeavingStatus($conversation)
    {
        $key = self::CONVERSATION_LEAVING_STATUS_KEY_PREFIX . $conversation['openId'];

        $cache = new RedisHelper();
        $cache->objectAdd($key, $conversation);

    }

    public static function isInLeavingStatus($openId)
    {
        $key = self::CONVERSATION_LEAVING_STATUS_KEY_PREFIX . $openId;

        $cache = new RedisHelper();
        $result = $cache->objectGet($key);

        if($result !== false){
            return true;
        } else {
            return false;
        }

    }

    public static function confirmLeaving($conversation, $message)
    {
        $confirmResult = false;
        if($message->MsgType == 'text') {
            $keyword = $message->Content;

            if($keyword == self::CONVERSATION_LEAVING_CONFIRM_KEYWORD) {
                //说明是退出确认：
                $confirmResult = true;
            }
        }

        //不管是否确认退出，都要清空leavingTalk状态：
        //因为用户发了非退出的消息，就表示不打算退出了。
        $key = self::CONVERSATION_LEAVING_STATUS_KEY_PREFIX . $conversation['openId'];
        $cache = new RedisHelper();
        $cache->objectRemove($key);

        return $confirmResult;
    }


}