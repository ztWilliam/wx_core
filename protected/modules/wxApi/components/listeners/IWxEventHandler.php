<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-5
 * Time: 下午8:41
 * To change this template use File | Settings | File Templates.
 */

interface IWxEventHandler {

    public static  function handleEvent($eventObj, $eventArgs) ;

}