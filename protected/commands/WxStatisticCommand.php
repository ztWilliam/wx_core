<?php
/**
 * 
 * User: william
 * Date: 15-4-28
 * Time: 下午10:30
 */
Yii::import('application.modules.statistic.components.*');
class WxStatisticCommand extends CConsoleCommand {

    /**
     * 统计用户活跃度的每日数据。
     * 建议每天凌晨执行。
     */
    public function actionGatherUserActivity(){
        try {
            UserStatistic::gatherActivityDaily();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function actionGatherUserSubscribed(){
        try {
            UserStatistic::gatherSubscriberDaily();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}