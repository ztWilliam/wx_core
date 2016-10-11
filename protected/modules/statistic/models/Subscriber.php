<?php
Yii::import('application.modules.wxApi.models.*');
/**
 * This is the model class for table "sta_subscriber".
 *
 * The followings are the available columns in table 'sta_subscriber':
 * @property integer $ghId
 * @property string $countDate
 * @property integer $subscribed
 * @property integer $unSubscribed
 * @property integer $returned
 * @property string $countTime
 * @property string $year
 * @property string $month
 * @property string $week
 */
class Subscriber extends CActiveRecord
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
        return 'sta_subscriber';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ghId, countDate, countTime, year, month, week', 'required'),
            array('ghId, subscribed, unSubscribed, returned', 'numerical', 'integerOnly'=>true),
            array('countDate', 'length', 'max'=>8),
            array('year', 'length', 'max'=>4),
            array('month, week', 'length', 'max'=>2),
            array('countTime', 'length', 'max'=>20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('ghId, countDate, year, month, week', 'safe', 'on'=>'search'),
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
            'subscribed' => 'Subscribed Count',
            'unSubscribed' => 'UnSubscribed Count',
            'returned' => 'Returned Count',
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

    private static function initStatisticsTable($countDate, $countTime)
    {
        $weekOfYear = date('W', strtotime($countDate));
        $year = date('Y', strtotime($countDate));
        $month = date('m', strtotime($countDate));

        //check whether it has been initialized before
        $statistic = self::model()->findByAttributes(array('countDate' => $countDate));
        if(isset($statistic)){
            //说明之前已经统计过，应当全部清零，并重置countTime:
            self::model()->updateAll(array(
                'countTime' => $countTime,
                'totalSubscribed' => 0,
                'subscribed' => 0,
                'unSubscribed' => 0,
                'returned' => 0,
            ), "countDate = '$countDate'");
        } else{
            //说明该日期的统计记录还没生成，需要初始化：
            $srcTableName = GhDefinition::model()->tableName();
            $tableName = self::model()->tableName();

            $sql = "insert into $tableName " .
                " ( ghId, countDate, countTime, totalSubscribed, subscribed, unSubscribed, returned, `year`, `month`, `week` )
                select id as ghId, :countDate as countDate, :countTime as countTime, 0 as totalSubscribed, 0 as subscribed, 0 as unSubscribed,
                0 as returned, :year as `year`, :month as `month`, :week as `week` from $srcTableName where delTag = 0 " ;

            $command = Yii::app()->db->createCommand($sql);
            $command->bindParam(':countDate', $countDate);
            $command->bindParam(':countTime', $countTime);
            $command->bindParam(':year', $year);
            $command->bindParam(':month', $month);
            $command->bindParam(':week', $weekOfYear);

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
            " ( ghId, countDate, totalSubscribed, subscribed, unSubscribed, returned )
            select ghId, :countDate as countDate, 0 as totalSubscribed, 0 as subscribed, 0 as unSubscribed,
                0 as returned from $srcTableName where countDate = :countDate " ;

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':countDate', $countDate);

        $command->execute();

    }

    private static function tempTableName()
    {
        return 'sta_subscriber_tmp';
    }

    public static function gatherData($countTime)
    {
        //统计时间的范围区间：左闭右开
        $fromTime = date('Y-m-d', strtotime($countTime . ' -1 day')) . ' 00:00:00';
        $toTime = date('Y-m-d', strtotime($countTime)) . ' 00:00:00';

        // 统计公众号的新增关注（人）数，放入临时表
        self::countSubscribed($fromTime, $toTime);

        // 统计公众号的累计关注（人）数，放入临时表
        self::countTotalSubscribed();

        // 统计公众号的取消关注（人次）数，放入临时表
        self::countUnSubscribed($fromTime, $toTime);

        // 统计公众号的重新关注（人次）数，放入临时表
        self::countReturned($fromTime, $toTime);

        // 将统计结果填入统计表（跟临时表交叉update）：
        self::fillResult();

    }

    private static function countTotalSubscribed()
    {
        $srcTableName = SubscribedUser::model()->tableName();
        $tmpTableName = self::tempTableName();

        //检索：首次关注的时间在 fromTime和toTime之间，且当前状态仍然是关注状态的
        $sql = "update $tmpTableName as t inner join ".
            "(select ghId, ifnull(count(openId), 0) as totalCount
            from $srcTableName
            where isSubscribed = :subscribeStatus group by ghId) as p
            on t.ghId = p.ghId set t.totalSubscribed = p.totalCount ";

        $command = Yii::app()->db->createCommand($sql);
        $subscribeStatus = SubscribedUser::IS_SUBSCRIBED_TRUE;
        $command->bindParam(':subscribeStatus', $subscribeStatus);

        $command->execute();
    }


    private static function countSubscribed($fromTime, $toTime)
    {
        $srcTableName = SubscribedUser::model()->tableName();
        $tmpTableName = self::tempTableName();

        //检索：首次关注的时间在 fromTime和toTime之间，且当前状态仍然是关注状态的
        $sql = "update $tmpTableName as t inner join ".
            "(select ghId, ifnull(count(openId), 0) as totalCount
            from $srcTableName
            where firstSubscribedTime >= :fromTime
            and firstSubscribedTime < :toTime
            and isSubscribed = :subscribeStatus group by ghId) as p
            on t.ghId = p.ghId set t.subscribed = p.totalCount ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':fromTime', $fromTime);
        $command->bindParam(':toTime', $toTime);
        $subscribeStatus = SubscribedUser::IS_SUBSCRIBED_TRUE;
        $command->bindParam(':subscribeStatus', $subscribeStatus);

        $command->execute();

    }

    private static function countUnSubscribed($fromTime, $toTime)
    {
        $srcTableName = SubscribedUser::model()->tableName();
        $tmpTableName = self::tempTableName();

        //检索 最后取消关注的时间在 fromTime 和 toTime之间，且最后状态是取消关注了的。
        $sql = "update $tmpTableName as t inner join ".
            "(select ghId, ifnull(count(openId), 0) as totalCount
            from $srcTableName
            where lastUnSubscribedTime >= :fromTime
            and lastUnSubscribedTime < :toTime
            and isSubscribed = :subscribeStatus group by ghId) as p
            on t.ghId = p.ghId set t.unSubscribed = p.totalCount ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':fromTime', $fromTime);
        $command->bindParam(':toTime', $toTime);

        $subscribeStatus = SubscribedUser::IS_SUBSCRIBED_FALSE;
        $command->bindParam(':subscribeStatus', $subscribeStatus);

        $command->execute();
    }

    private static function countReturned($fromTime, $toTime)
    {
        $srcTableName = SubscribedUser::model()->tableName();
        $tmpTableName = self::tempTableName();

        //检索 最后一次关注的时间在 fromTime 和 toTime之间，且最后状态是关注了，且关注次数大于1的。
        $sql = "update $tmpTableName as t inner join ".
            "(select ghId, ifnull(count(openId), 0) as totalCount
            from $srcTableName
            where lastSubscribedTime >= :fromTime
            and lastSubscribedTime < :toTime
            and subscribedCount > 1
            and isSubscribed = :subscribeStatus group by ghId) as p
            on t.ghId = p.ghId set t.returned = p.totalCount ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':fromTime', $fromTime);
        $command->bindParam(':toTime', $toTime);

        $subscribeStatus = SubscribedUser::IS_SUBSCRIBED_TRUE;
        $command->bindParam(':subscribeStatus', $subscribeStatus);

        $command->execute();

    }

    private static function fillResult()
    {
        $tableName = self::model()->tableName();
        $tmpTableName = self::tempTableName();

        $sql = "update $tableName as t inner join ".
            "$tmpTableName as p on
            t.ghId = p.ghId and t.countDate = p.countDate set
            t.totalSubscribed = p.totalSubscribed,
            t.subscribed = p.subscribed,
            t.unSubscribed = p.unSubscribed,
            t.returned = p.returned ";

        $command = Yii::app()->db->createCommand($sql);

        $command->execute();

    }

}