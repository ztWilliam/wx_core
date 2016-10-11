<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhengtuoqingdao
 * Date: 12-7-21
 * Time: 下午5:06
 * To change this template use File | Settings | File Templates.
 */
class ExceptionCode
{

    const NORMAL_EXCEPTION = 1; //一般异常信息

    const UNACTIVATED = 2; //未激活手机

    const MULTI_LOGIN = 3;  //重复登录

    const TOKEN_INVALID = 9; //token失效，需重新登录
    const SESSION_INVALID = 99; //session失效，需重新登录
    const PARAMETER_NOT_SUPPORT = 901;  //参数不支持的错误

    const ARTICLE_NOT_FOUND = 10001; //文章异常,找不到文章
    const SEND_MESSAGE_FAILURE = 10010; //消息异常，推送消息失败

}
