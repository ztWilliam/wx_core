<?php

/**
 * This is the model class for table "wxa_template_failed".
 *
 * The followings are the available columns in table 'wxa_template_failed':
 * @property integer $ghId
 * @property integer $msgId
 * @property string $templateId
 * @property string $openId
 * @property string $sendTime
 * @property string $failedReason
 * @property string $content
 */
class TemplateFailed extends CActiveRecord
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
		return 'wxa_template_failed';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ghId, msgId, openId, templateId, sendTime, failedReason, content', 'required'),
			array('ghId, msgId', 'numerical', 'integerOnly'=>true),
            array('templateId, openId', 'length', 'max'=>64),
            array('failedReason', 'length', 'max'=>256),
            array('sendTime', 'length', 'max'=>20),
            array('content', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ghId, templateId, openId, msgId', 'safe', 'on'=>'search'),
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
			'openId' => 'Open ID',
			'msgId' => 'Message ID',
			'sendTime' => 'Send Time',
			'failedReason' => 'Failed Reason',
			'content' => 'Content',
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
		$criteria->compare('openId',$this->openId,true);
		$criteria->compare('msgId',$this->msgId,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function saveFailureInfo($ghId, $templateId, $msgId, $openId, $sendTime, $failedReason, $sendContent)
    {
        $model = new TemplateFailed();

        $model->ghId = $ghId;
        $model->msgId = $msgId;
        $model->templateId = $templateId;
        $model->openId = $openId;
        $model->sendTime = $sendTime;
        $model->failedReason = $failedReason;

        $content  = FastJSON::encode($sendContent);
        $model->content = $content;

        if(!$model->save()) {
            LogWriter::logModelSaveError($model, __METHOD__, array(
                'ghId' => $ghId,
                'msgId' => $msgId,
                'openId' => $openId,
                'content' => $content,
                'failedReason' => $failedReason,
            ));
            throw new Exception('TemplateFailed data saved error.');
        }
    }

}