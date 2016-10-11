<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-18
 * Time: 上午10:28
 * To change this template use File | Settings | File Templates.
 */
class WxMenuGroup
{
    var $name;
    var $sub_button;

    public function __construct($_name, $subButtons = array()){
        $this->name = $_name;
        $this->sub_button = $subButtons;
    }
}
