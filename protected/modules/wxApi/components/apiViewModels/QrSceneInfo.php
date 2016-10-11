<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-11
 * Time: 下午1:20
 * To change this template use File | Settings | File Templates.
 */
class QrSceneInfo
{
    var $sceneId;  //int
    var $expireAt;      //string   此场景的失效时间, 永久的二维码，此属性值为 'none'

    public function __construct($sId, $expire = 'none'){
        $this->sceneId = $sId;
        $this->expireAt = $expire;
    }
}
