<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-9
 * Time: 上午10:46
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.components.apiCommon.*');

class AdminController extends ApiBaseController {

    public function actionRegisterGh(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::registerGh($_POST, $this->module->url);

        $this->returnJsonUtf8($result);

    }

    public function actionGetGhInfo(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::getGhInfo($_GET, $this->module->url);

        $this->returnJsonUtf8($result);

    }

    public function actionResetAppSecret() {
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::resetAppSecret($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionAddLimitQrScene(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxQrManager::addLimitQrScene($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionRemoveQrScene(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxQrManager::removeQrScene($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionAddTempQrScene(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxQrManager::addTempQrScene($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionGetQrImageUrl(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxQrManager::getQrImageUrl($_GET);

        $this->returnJsonUtf8($result);

    }

    /**
     * 将菜单设置，更新到微信公众平台
     *
     */
    public function actionRefreshMenu(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::refreshMenu($_POST);

        $this->returnJsonUtf8($result);
    }

    /**
     * 获取指定公众号当前在微信服务器上的菜单设置
     */
    public function actionGetMenusOnline(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::getMenusOnline($_GET);

        $this->returnJsonUtf8($result);
    }

    public function actionRemoveAllMenus(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::removeAllMenus($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionAddMainMenu(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::addMainMenu($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionRemoveMainMenu(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::removeMainMenu($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionAddSubMenu(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::addSubMenu($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionRemoveSubMenu(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMenuManager::removeSubMenu($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionGetWxUserInfo(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxUserManager::getUserInfo($_GET);

        $this->returnJsonUtf8($result);
    }

    public function actionSendCustomTextMessage(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMessageExpress::sendCustomTextMessage($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionGetShortUrl(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::getShortUrl($_GET);

        $this->returnJsonUtf8($result);
    }

    public function actionOpenConversation(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxConversationManager::openConversation($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionCloseConversation(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxConversationManager::closeConversation($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionSetMessageHandler(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMessageExpress::setGhMessageHandler($_POST);

        $this->returnJsonUtf8($result);

    }

    public function actionSetSubscribeHandler(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMessageExpress::setSubscribeHandler($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionSetUnSubscribeHandler(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMessageExpress::setUnSubscribeHandler($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionSetUrlVerifiedHandler(){
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxMessageExpress::setUrlVerifiedHandler($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionGetFileUrl() {
        // 获取文件的url
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxFileManager::getFileUrl($_GET);

        $this->returnJsonUtf8($result);

    }

    public function actionSetFileAccess() {
        //todo 修改文件的访问权限： 私有还是公用？

    }

    public function actionGetJsApiSignPackage() {
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxJsApiManager::getJsApiSignPackage($_GET);

        $this->returnJsonUtf8($result);
    }

    public function actionTemplateRegister() {
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxTemplateManager::registerTemplate($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionTemplateMessageSend() {
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxTemplateManager::sendTemplateMessage($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionTemplateMessageBatchSend() {
        $this->checkClientToken($_POST[self::CLIENT_TOKEN_ID]);

        $result = WxTemplateManager::batchSendTemplateMessage($_POST);

        $this->returnJsonUtf8($result);
    }

    public function actionGetPageAuthAccessToken() {
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::getOAuthAccessToken($_GET);

        $this->returnJsonUtf8($result);
    }

    public function actionGetAuthUrl(){
        $this->checkClientToken($_GET[self::CLIENT_TOKEN_ID]);

        $result = WxGhManager::getOAuthUrl($_GET);

        $this->returnJsonUtf8($result);
    }
}