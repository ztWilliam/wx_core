<?php

Yii::import('application.modules.wxApi.components.*');
Yii::import('application.modules.wxApi.components.util.*');

/**
 * This is the model class for table "wxa_gh_message_handler".
 *
 * The followings are the available columns in table 'wxa_gh_message_handler':
 * @property integer $ghId
 * @property string $ghInitialId
 * @property string $msgType
 * @property string $handler
 */
class GhMessageHandler extends CActiveRecord
{
    /**
     * Returns the static model of the specified GhMessageHandler class.
     * @return GhMessageHandler the static model class
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
        return 'wxa_gh_message_handler';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ghId, handler, ghInitialId', 'required'),
            array('ghId', 'numerical', 'integerOnly'=>true),
            array('ghInitialId', 'length', 'max'=>20),
            array('handler', 'length', 'max'=>100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('ghId, ghInitialId, handler', 'safe', 'on'=>'search'),
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
            'ghId' => 'GH',
            'ghInitialId' => 'Gh Initial Id',
            'handler' => 'Handler',
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
        $criteria->compare('ghInitialId',$this->classAlias,true);
        $criteria->compare('handler',$this->handlerType,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    const MSG_TYPE_MESSAGE = 'message';
    const MSG_TYPE_SUBSCRIBE = 'subscribe';
    const MSG_TYPE_UNSUBSCRIBE = 'unsubscribe';
    const MSG_TYPE_URL_VERIFIED = 'verified';

    public static function saveMessageHandler($ghId, $ghInitialId, $handler)
    {
        $msgType = self::MSG_TYPE_MESSAGE;
        $handlerObj = self::saveHandler($ghId, $ghInitialId, $handler, $msgType);

        return $handlerObj;
    }

    public static function findMessageHandler($id)
    {

        $handlerObj = self::model()->findByPk(array('ghId' => $id, 'msgType' => self::MSG_TYPE_MESSAGE));

        return $handlerObj;
    }

    public static function findSubscribeHandler($id)
    {

        $handlerObj = self::model()->findByPk(array('ghId' => $id, 'msgType' => self::MSG_TYPE_SUBSCRIBE));

        return $handlerObj;
    }

    public static function findUnSubscribeHandler($id)
    {
        $handlerObj = self::model()->findByPk(array('ghId' => $id, 'msgType' => self::MSG_TYPE_UNSUBSCRIBE));

        return $handlerObj;
    }

    public static function findVerifiedHandler($id)
    {
        $handlerObj = self::model()->findByPk(array('ghId' => $id, 'msgType' => self::MSG_TYPE_URL_VERIFIED));

        return $handlerObj;
    }

    public static function saveSubscribeHandler($ghId, $ghInitialId, $handler)
    {
        $msgType = self::MSG_TYPE_SUBSCRIBE;
        $handlerObj = self::saveHandler($ghId, $ghInitialId, $handler, $msgType);

        return $handlerObj;

    }

    public static function saveUnSubscribeHandler($ghId, $ghInitialId, $handler)
    {
        $msgType = self::MSG_TYPE_UNSUBSCRIBE;
        $handlerObj = self::saveHandler($ghId, $ghInitialId, $handler, $msgType);

        return $handlerObj;

    }

    public static function saveUrlVerifiedHandler($ghId, $ghInitialId, $handler)
    {
        $msgType = self::MSG_TYPE_URL_VERIFIED;
        $handlerObj = self::saveHandler($ghId, $ghInitialId, $handler, $msgType);

        return $handlerObj;
    }


    /**
     * @param $ghId
     * @param $ghInitialId
     * @param $handler
     * @param $msgType
     * @return GhMessageHandler
     * @throws Exception
     */
    private static function saveHandler($ghId, $ghInitialId, $handler, $msgType)
    {
        //检查handler是否有效
        WxCommonFunction::checkHandler($handler);

        $handlerObj = self::model()->findByPk(array('ghId' => $ghId, 'msgType' => $msgType));
        if (!isset($handlerObj)) {
            $handlerObj = new GhMessageHandler();
        }

        $handlerObj->ghId = $ghId;
        $handlerObj->ghInitialId = $ghInitialId;
        $handlerObj->msgType = $msgType;
        $handlerObj->handler = $handler;

        if (!$handlerObj->save()) {
            LogWriter::logModelSaveError($handlerObj, __METHOD__, array(
                'ghId' => $ghId,
                'ghInitialId' => $ghInitialId,
                'msgType' => $msgType,
                'handler' => $handler,
            ));
            throw new Exception('保存消息处理接口对象失败');
        }
        return $handlerObj;
    }


}