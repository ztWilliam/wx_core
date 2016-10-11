<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-10-7
 * Time: 下午7:20
 * To change this template use File | Settings | File Templates.
 */

class WxJsonTemplate {

    /**
     *
     * 向微信发送客服信息的文本
     *
    {
    "touser":"OPENID",
    "msgtype":"text",
    "text":
      {
         "content":"Hello World"
      }
    }
     *
     * @param $toUser
     * @param $content
     */
    public static function customTextMessage($toUser, $content)
    {
        $temp = array(
            "touser" => $toUser,
            "msgtype" => "text",
            "text" => array(
                "content" => $content,
            ),
        );

        return FastJSON::encode($temp);
    }
}