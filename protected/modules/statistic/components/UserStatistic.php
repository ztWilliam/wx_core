<?php
/**
 *
 * User: william
 * Date: 15-4-28
 * Time: 下午12:10
 */
Yii::import('application.modules.statistic.models.*');
Yii::import('application.modules.wxApi.components.*');
Yii::import('application.modules.wxApi.components.util.*');
class UserStatistic {

    public static function gatherActivityDaily(){
        $countDate = date('Ymd');
        $countTime = date('Y-m-d H:i:s');

        try {
            // 初始化待统计日期的公众号记录
            UserActivity::initGathering($countDate, $countTime);

            // 将统计结果填入统计结果表中 （跟临时表交叉update）
            UserActivity::gatherData();

        } catch (WxException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            WxLogWriter::error($ex->getMessage());
            throw $ex;
        }

    }

    public static function gatherSubscriberDaily(){
        $countDate = date('Ymd', strtotime(date('Y-m-d') . ' -1 day'));
        $countTime = date('Y-m-d H:i:s');

        try {
            Subscriber::initGathering($countDate, $countTime);


            Subscriber::gatherData($countTime);

        }catch (WxException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            WxLogWriter::error($ex->getMessage());
            throw $ex;
        }

    }
}