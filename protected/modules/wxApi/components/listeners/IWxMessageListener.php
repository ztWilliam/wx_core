<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-5
 * Time: 上午10:39
 * To change this template use File | Settings | File Templates.
 */
interface IWxMessageListener
{

    /**
     * 根据微信发来的普通消息体，生成消息的返回内容。
     * 返回的内容需要符合微信消息的模板定义。
     *
     * @param $msgObject
     * @param array $params
     * @return string
     */
    public function replyMessage($msgObject, $params = array());

    public function listenerDesc();

    public function onExitListening($fromUser, $eventArgs = array());

}
