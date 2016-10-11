<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-18
 * Time: 上午10:29
 * To change this template use File | Settings | File Templates.
 */
class WxMenuItem
{
    var $name;
    var $type;
    var $key;
    var $handler;

    public function __construct($_name, $_type, $_key, $_handler){
        $this->name = $_name;
        $this->handler = $_handler;
        $this->key = $_key;
        $this->type = $_type;
    }
}
