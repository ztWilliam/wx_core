<?php

Yii::import('application.modules.wxApi.models.*');
/**
 * This is the model class for table "sta_user_activity".
 *
 * The followings are the available columns in table 'sta_user_activity':
 * @property integer $ghId
 * @property string $countDate
 * @property integer $activeIn24Hours
 * @property integer $activeIn48Hours
 * @property integer $activeInOneWeek
 * @property integer $activeInOneMonth
 * @property string $countTime
 */
class UserActivity extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return UserActivity the static model class
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
        return 'sta_user_activity';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ghId, countDate, countTime', 'required'),
            array('ghId, activeIn24Hours, activeIn48Hours, activeInOneWeek, activeInOneMonth', 'numerical', 'integerOnly'=>true),
            array('countDate', 'length', 'max'=>8),
            array('countTime', 'length', 'max'=>20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('ghId, countDate', 'safe', 'on'=>'search'),
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
            'countDate' => 'Count Date',
            'countTime' => 'Count Time',
            'activeIn24Hours' => 'Active in 24 hours',
            'activeIn48Hours' => 'Active in 48 hours',
            'activeInOneWeek' => 'Active in 1 week',
            'activeInOneMonth' => 'Active in 1 month',
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
        $criteria->compare('countDate',$this->countDate,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public static function initGathering($countDate, $countTime)
    {
        // 初始化待统计日期的公众号记录
        self::initStatisticsTable($countDate, $countTime);

        // 清空临时表，并初始化公众号信息
        self::resetTempTable($countDate);

    }

    public static function gatherData()
    {
        $oneDayBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s' . ' -24 hours')));
        $twoDaysBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s' . ' -48 hours')));
        $oneWeekBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s' . ' -7 days')));
        $oneMonthBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s' . ' -1 month')));

        // 统计公众号的24小时活跃数，放入临时表
        self::countBy24Hours($oneDayBefore);

        // 统计公众号的48小时活跃数，放入临时表
        self::countBy48Hours($twoDaysBefore);

        // 统计公众号的周活跃数，放入临时表
        self::countByWeek($oneWeekBefore);

        // 统计公众号的月活跃数，放入临时表
        self::countByMonth($oneMonthBefore);

        // 将统计结果数据填入正式的统计表
        self::fillResult();

    }

    private static function initStatisticsTable($countDate, $countTime)
    {
        //check whether it has been initialized before
        $statistic = self::model()->findByAttributes(array('countDate' => $countDate));
        if(isset($statistic)){
            //说明之前已经统计过，应当全部清零，并重置countTime:
            self::model()->updateAll(array(
                'countTime' => $countTime,
                'activeIn24Hours' => 0,
                'activeIn48Hours' => 0,
                'activeInOneWeek' => 0,
                'activeInOneMonth' => 0,
            ), "countDate = '$countDate'");
        } else{
            $srcTableName = GhDefinition::model()->tableName();
            $tableName = self::model()->tableName();

            //说明该日期的统计记录还没生成，需要初始化：
            $sql = "insert into $tableName " .
                " ( ghId, countDate, countTime, activeIn24Hours, activeIn48Hours, activeInOneWeek, activeInOneMonth )
                select id as ghId, :countDate as countDate, :countTime as countTime, 0 as activeIn24Hours, 0 as activeIn48Hours,
                0 as activeInOneWeek, 0 as activeInOneMonth from $srcTableName where delTag = 0 " ;

            $command = Yii::app()->db->createCommand($sql);
            $command->bindParam(':countDate', $countDate);
            $command->bindParam(':countTime', $countTime);

            $command->execute();

        }
    }

    private static function resetTempTable($countDate)
    {
        $srcTableName = self::tableName();
        $tmpTableName = self::tempTableName();

        $sql = "delete from $tmpTableName";
        $command = Yii::app()->db->createCommand($sql);
        $command->execute();

        $sql = "insert into $tmpTableName " .
            " ( ghId, countDate, activeIn24Hours, activeIn48Hours, activeInOneWeek, activeInOneMonth )
            select ghId, :countDate as countDate, 0 as activeIn24Hours, 0 as activeIn48Hours,
            0 as activeInOneWeek, 0 as activeInOneMonth from $srcTableName where countDate = :countDate " ;


        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':countDate', $countDate);

        $command->execute();

    }

    private static function tempTableName()
    {
        return self::tableName() . '_tmp';
    }

    private static function countBy24Hours($oneDayBefore)
    {
        $targetColumn = 'activeIn24Hours';

        self::updateTempTable($targetColumn, $oneDayBefore);
    }

    private static function countBy48Hours($twoDaysBefore)
    {
        $targetColumn = 'activeIn48Hours';

        self::updateTempTable($targetColumn, $twoDaysBefore);

    }

    private static function countByWeek($oneWeekBefore)
    {
        $targetColumn = 'activeInOneWeek';

        self::updateTempTable($targetColumn, $oneWeekBefore);

    }

    private static function countByMonth($oneMonthBefore)
    {
        $targetColumn = 'activeInOneMonth';

        self::updateTempTable($targetColumn, $oneMonthBefore);

    }


    private static function updateTempTable($targetColumn, $afterTime)
    {
        $srcTableName = ActiveUser::model()->tableName();
        $tmpTableName = self::tempTableName();

        $sql = "update $tmpTableName as t inner join ".
            "(select ghId, ifnull(count(openId), 0) as totalCount
            from $srcTableName where lastAccessTime >= :afterTime group by ghId) as p
            on t.ghId = p.ghId set t.$targetColumn = p.totalCount ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':afterTime', $afterTime);

        $command->execute();

    }

    private static function fillResult()
    {
        $tableName = self::model()->tableName();
        $tmpTableName = self::tempTableName();

        $sql = "update $tableName as t inner join ".
            "$tmpTableName as p on
            t.ghId = p.ghId and t.countDate = p.countDate set
            t.activeIn24Hours = p.activeIn24Hours,
            t.activeIn48Hours = p.activeIn48Hours,
            t.activeInOneWeek = p.activeInOneWeek,
            t.activeInOneMonth = p.activeInOneMonth ";

        $command = Yii::app()->db->createCommand($sql);

        $command->execute();

    }


}