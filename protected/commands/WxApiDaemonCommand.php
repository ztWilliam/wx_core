<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-12-18
 * Time: 下午4:22
 * To change this template use File | Settings | File Templates.
 */
Yii::import('application.modules.wxApi.components.*');

class WxApiDaemonCommand  extends CConsoleCommand {

    /**
     * 将微信服务器接收到的用户上传文件，转移到我们的云服务器上。
     * 建议每天执行一次或 2-3次
     * （若文件数量过多，建议每天执行三次， 并通过maxHours指定每次转移的最大运行时间）
     *
     * @param int $maxHours  0 为不限制；
     */
    public function actionTransferFiles($maxHours = 0){

        try {
            WxFileManager::transferFileToCloud($maxHours);
        } catch(Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * 检查超过48小时没交互的微信用户，将过期的置为非在线状态。
     * 建议每3-5分钟运行一次本命令
     */
    public function actionClearExpiredOnlineUsers() {
        try {
            WxUserManager::clearExpiredOnlineUsers();

        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * 将超出一个月没访问过的用户，从活跃记录中移除。
     * 建议每天凌晨执行一次
     */
    public function actionClearExpiredActiveUsers() {
        try {
            WxUserManager::clearExpiredActiveUsers();

        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

    }
}