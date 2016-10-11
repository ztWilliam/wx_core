<?php

/**
 * This is the model class for table "wxa_template".
 *
 * The followings are the available columns in table 'wxa_template':
 * @property integer $id
 * @property integer $ghId
 * @property string $templateId
 * @property string $successHandler
 * @property string $failedHandler
 */
class Template extends CActiveRecord
{

    /**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
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
		return 'wxa_template';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, templateId, successHandler, failedHandler', 'required'),
			array('ghId', 'numerical', 'integerOnly'=>true),
            array('successHandler, failedHandler', 'length', 'max'=>256),
            array('templateId', 'length', 'max'=>64),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, templateId', 'safe', 'on'=>'search'),
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
			'templateId' => 'Template ID',
			'successHandler' => 'Success Handler (url)',
			'failedHandler' => 'Failed Handler (url)',
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
		$criteria->compare('templateId',$this->templateId,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    const COLOR_TOP_DEFAULT = "#FF0000";
    const COLOR_CONTENT_NORMAL = "#173177";
    const COLOR_CONTENT_PRIMARY = "#FF0000";

    const CACHE_KEY_PREFIX = "wx_tmpl_msg_";

    public static function saveTemplate($ghId, $templateId, $successHandler = '', $failedHandler = '')
    {
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($templateId)){
            throw new WxException('请输入微信消息模板的id（在微信后台查看）');
        }
        if(empty($successHandler)){
            $successHandler = WxCommonDef::FIELD_NOT_DEFINED;
        }
        if(empty($failedHandler)) {
            $failedHandler = WxCommonDef::FIELD_NOT_DEFINED;
        }

        $obj = self::model()->findByAttributes(array('ghId' => $ghId, 'templateId' => $templateId));
        if(!isset($obj)) {
            $obj = new Template();
            $obj->ghId = $ghId;
            $obj->templateId = $templateId;
        }

        $obj->successHandler = $successHandler;
        $obj->failedHandler = $failedHandler;

        if(!$obj->save()) {
            LogWriter::logModelSaveError($obj, __METHOD__, array(
                'ghId' => $ghId,
                'templateId' => $templateId,
                'successHandler' => $successHandler,
                'failedHandler' => $failedHandler,
            ));
            throw new Exception('Template 保存失败。');
        }

        return $obj;
    }

    public static function sendTemplateMessage($ghId, $toUser, $templateId, $url, $contentArray, $topColor)
    {
        $sendContent = self::constructMessageBody($toUser, $templateId, $url, $contentArray, $topColor);

        $accessToken = GhAccessToken::getAccessToken($ghId);

        $result = WxCgiCaller::sendTemplateMessage($accessToken, FastJSON::encode($sendContent));

        // 将msg的发送记录在缓存中，等待反馈事件
        self::saveMessageToCache($result['msgid'], $sendContent);

        return $result['msgid'];
    }

    private static function constructMessageBody($toUser, $templateId, $url, $contentArray, $topColor = '')
    {
        if(empty($topColor)) {
            $topColor = self::COLOR_TOP_DEFAULT;
        }

        $sendContent = array(
            'touser' => $toUser,
            'template_id' => $templateId,
            'url' => $url,
            'topcolor' => $topColor,
            'data' => $contentArray,
        );

        return $sendContent;
    }

    private static function saveMessageToCache($msgId, $sendContent)
    {
        $msgObj = array(
            'msgId' => $msgId,
            'sendTime' => date('Y-m-d H:i:s'),
            'sendContent' => $sendContent,
        );

        $key = self::CACHE_KEY_PREFIX . $msgId;
        $cache = new RedisHelper();

        $cache->setString($key, FastJSON::encode($msgObj));
    }

    public static function processMessageSucceed($ghInfo, $msgId)
    {
        // 从缓存中找到该msgId
        $msgObj = self::messageInCache($msgId);
        if(!isset($msgObj)) {
            return;
        }

        // 从缓存中删除该msgId
        self::removeMessageInCache($msgId);

        //记录反馈时间，用于跟踪消息的发送／反馈效率。
        WxLogWriter::trace("Template message success feedback at " . date('Y-m-d H:i:s') . ' which msgId is ' . $msgId);

        // 如果有successHandler，调用之。
        $templateId = $msgObj['sendContent']['template_id'];
        $templateObj = self::findTemplate($ghInfo->id, $templateId);
        if(isset($templateObj)){
            //注册过的模板：
            if($templateObj->successHandler !== WxCommonDef::FIELD_NOT_DEFINED) {
                $postData = array(
                    'msgId' => $msgId,
                );

                $handler = $templateObj->successHandler;
                try {
                    WxCommonFunction::callEventHandler($handler, WxCommonDef::HANDLER_TYPE_URL, $postData);
                } catch (Exception $ex) {
                    WxLogWriter::warning("Template [$templateId] successHandler [$handler] called error : " . $ex->getMessage());
                }
            }
        }
    }

    public static function processMessageFailed($ghInfo, $msgId, $status)
    {
        // 从缓存中找到该msgId
        $msgObj = self::messageInCache($msgId);
        if(!isset($msgObj)) {
            return;
        }

        // 从缓存中删除该msgId
        self::removeMessageInCache($msgId);

        //记录反馈时间，用于跟踪消息的发送／反馈效率。
        WxLogWriter::trace("Template message failure feedback at " . date('Y-m-d H:i:s') . ' which msgId is ' . $msgId);

        $templateId = $msgObj['sendContent']['template_id'];
        try {
            // 在TemplateFailed中保存失败记录
            TemplateFailed::saveFailureInfo($ghInfo->id, $templateId, $msgId,
                $msgObj['sendContent']['touser'], $msgObj['sendTime'], $status, $msgObj['sendContent']);

            // 如果有failedHandler，调用之
            $templateObj = self::findTemplate($ghInfo->id, $templateId);
            if(isset($templateObj)){
                //注册过的模板：
                if($templateObj->failedHandler !== WxCommonDef::FIELD_NOT_DEFINED) {
                    $postData = array(
                        'msgId' => $msgId,
                        'failedReason' => $status,
                    );

                    $handler = $templateObj->failedHandler;

                    WxCommonFunction::callEventHandler($handler, WxCommonDef::HANDLER_TYPE_URL, $postData);
                }
            }

        } catch (Exception $ex) {
            WxLogWriter::warning("Template [$templateId] failure processed error : " . $ex->getMessage());
        }

    }

    private static function messageInCache($msgId)
    {
        $key = self::CACHE_KEY_PREFIX . $msgId;
        $cache = new RedisHelper();

        $msgBody = $cache->getString($key);

        if($msgBody == false ){
            return null;
        }

        return FastJSON::decode($msgBody);
    }

    private static function removeMessageInCache($msgId)
    {
        $key = self::CACHE_KEY_PREFIX . $msgId;
        $cache = new RedisHelper();

        $cache->del($key);
    }

    private static function findTemplate($ghId, $template_id)
    {
        $obj = self::model()->findByAttributes(array('ghId' => $ghId, 'templateId' => $template_id));

        return $obj;
    }

}