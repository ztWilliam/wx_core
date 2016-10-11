<?php
Yii::import('application.modules.wxApi.components.*');

class WeixinController extends CController
{
	public function actionIndex()
	{
		$this->render('index');
	}

    public function actionReply() {


        if(isset($_GET['echostr'])) {
            $this->checkValidUrl($_GET);
            exit;
        }
        $ghInfo = $this->getGhInfoByReq($_GET);

        $this->processMessage($ghInfo);
    }

    private function checkValidUrl($params) {
        $ghInfo = $this->getGhInfoByReq($params);

        $message = new WxMessageHandler();

        $message->valid($_GET, $ghInfo);

    }

    private function processMessage($ghInfo) {
        $message = new WxMessageHandler();
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        $message->responseMsg($postStr, $ghInfo);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getGhInfoByReq($params)
    {
        $req = $params['req'];

        $key = TextUtility::getLastElement($req, '/');
        $ghInfo = GhDefinition::findGhByUri($key);
        return $ghInfo;
    }
}