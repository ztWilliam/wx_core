<?php

/**
 * This is the model class for table "wxa_gh_js_api_ticket".
 *
 * The followings are the available columns in table 'wxa_gh_js_api_ticket':
 * @property integer $ghId
 * @property string $ticket
 * @property string $expireAt
 */
class GhJsApiTicket extends CActiveRecord
{
    const TICKET_NOT_SET = 'none';

    //TOKEN失效的秒数，微信默认为7200秒，为了消除网络调用的时间损耗误差，将失效时间定为7000秒，确保在access token失效前，重新获取。
    const TOKEN_EXPIRES_IN_SECONDS = 7000;

    /**
	 * Returns the static model of the specified AR class.
	 * @return GhJsApiTicket the static model class
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
		return 'wxa_gh_js_api_ticket';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, ticket, expireAt', 'required'),
			array('ghId', 'numerical', 'integerOnly'=>true),
            array('ticket', 'length', 'max'=>256),
            array('expireAt', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, ticket, expireAt', 'safe', 'on'=>'search'),
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
			'ticket' => 'Ticket',
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
		$criteria->compare('ticket',$this->ticket,true);
		$criteria->compare('expireAt',$this->expireAt,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    private static function findTicketObj($ghId)
    {
        return self::model()->findByPk($ghId);
    }

    public static function getTicket($ghId)
    {
        $ticketObj = self::findTicketObj($ghId);
        if(!isset($ticketObj)) {
            $ticketObj = self::createBlankTicket($ghId);
        }

        //检查公众号下面的accessToken是否有效：
        if($ticketObj->ticket !== self::TICKET_NOT_SET && $ticketObj->expireAt >= date('Y-m-d H:i:s')) {
            return $ticketObj->ticket;
        }

        //若无效，重新申请一个，保存，并返回
        $accessToken = GhAccessToken::getAccessToken($ghId);
        $newTicket = WxCgiCaller::getJsApiTicket($accessToken);
        if(empty($newTicket)){
            throw new WxException('无法获取新的JSApiTicket');
        }

        $ticketObj->ticket = $newTicket;
        $ticketObj->expireAt = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + ' . self::TOKEN_EXPIRES_IN_SECONDS . ' second '));

        if(!$ticketObj->save()){
            LogWriter::logModelSaveError($ticketObj, __METHOD__, array(
                'new ticket' => $newTicket,
                'ghId' => $ghId,
            ));
            throw new Exception('JsApiTicket更新失败');
        }

        return $newTicket;
    }

    private static function createBlankTicket($ghId)
    {
        $ticketObj = new GhJsApiTicket();
        $ticketObj->ghId = $ghId;
        $ticketObj->ticket = self::TICKET_NOT_SET;
        $ticketObj->expireAt = date('Y-m-d H:i:s');

        return $ticketObj;
    }
}