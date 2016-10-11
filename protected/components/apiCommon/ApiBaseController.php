<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-11-14
 * Time: 下午3:10
 * To change this template use File | Settings | File Templates.
 */

class ApiBaseController extends CController {
    const CLIENT_TOKEN_ID = 'APIClientToken';

    protected function returnJsonUtf8($result) {
        header("Content-Type: application/json; charset=utf-8", true);
        echo FastJSON::encode($result);
    }

    protected function checkClientToken($token)
    {
        $clientToken = $token;
        if (!ApiClientValidator::isValidClient($clientToken, $_SERVER)) {
            throw new CHttpException(404);
        }
    }

}