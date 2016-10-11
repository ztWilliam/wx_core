<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-10
 * Time: 下午4:22
 * To change this template use File | Settings | File Templates.
 */

class WxGhInfo {
    var $id;
    var $ghId;
    var $token;
    var $url;
    var $appId;
    var $appSecret;
    var $ghName;
    var $ghDesc;

    public function __construct($_id, $_ghId, $_token, $_url, $_appId, $_appSecret, $_ghName, $_ghDesc){
        $this->id = $_id;
        $this->ghId = $_ghId;
        $this->ghName = $_ghName;
        $this->ghDesc = empty($_ghDesc) ? '' : $_ghDesc;
        $this->appId = $_appId;
        $this->appSecret = $_appSecret;
        $this->token = $_token;
        $this->url = $_url;
    }

}