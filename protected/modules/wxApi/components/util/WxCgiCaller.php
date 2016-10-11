<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-11
 * Time: 下午3:34
 * To change this template use File | Settings | File Templates.
 */
class WxCgiCaller
{
    /**
     * 获取二维码的Ticket
     *
    http请求方式: POST
    URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
    POST数据格式：json
    POST数据例子：
    临时二维码：{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
    永久二维码：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}

    正确的Json返回结果:
    {"ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhvMVp4NDNMRnNRAAIEesLvUQMECAcAAA==","expire_seconds":1800}
    错误的Json返回示例:
    {"errcode":40013,"errmsg":"invalid appid"}

     */
    const QR_TICKET_CREATE_URL = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s";

    /**
     * 获取二维码图片的url
    HTTP GET请求（请使用https协议）
    提醒：TICKET记得进行UrlEncode
    ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。

    HTTP头（示例）如下：
    Accept-Ranges:bytes
    Cache-control:max-age=604800
    Connection:keep-alive
    Content-Length:28026
    Content-Type:image/jpg
    Date:Wed, 16 Oct 2013 06:37:10 GMT
    Expires:Wed, 23 Oct 2013 14:37:10 +0800
    Server:nginx/1.4.1
     *
     * 错误情况下（如ticket非法）返回HTTP错误码404。
     */
    const QR_IMAGE_GET_URL = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s"; //TICKET需要urlEncode

    /**
     * 获取AccessToken的url
     *
    http请求方式: GET

    返回说明

    正常情况下，微信会返回下述JSON数据包给公众号：
    {"access_token":"ACCESS_TOKEN","expires_in":7200}

    错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
    {"errcode":40013,"errmsg":"invalid appid"}
     */
    const ACCESS_TOKEN_GET_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";


    /**
     * 上传媒体文件的接口url
    http请求方式: POST/FORM
    http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE
    调用示例（使用curl命令，用FORM表单方式上传一个多媒体文件）：
    curl -F media=@test.jpg "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE"
     *
     * 返回说明
    正确情况下的返回JSON数据包结果如下：
    {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     *
     * 错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
    {"errcode":40004,"errmsg":"invalid media type"}
     *
     * 注意事项
    上传的多媒体文件有格式和大小限制，如下：

    图片（image）: 1M，支持JPG格式
    语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
    视频（video）：10MB，支持MP4格式
    缩略图（thumb）：64KB，支持JPG格式
    媒体文件在后台保存时间为3天，即3天后media_id失效。
     *
     */
    const MEDIA_UPLOAD_URL = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s";

    /**
     * 下载媒体文件的接口
     * 注意：视频不能下载
     *
     * http请求方式: GET
     *
     *请求示例（示例为通过curl命令获取多媒体文件）
    curl -I -G "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID"
     *
     *
     返回说明

    正确情况下的返回HTTP头如下：

    HTTP/1.1 200 OK
    Connection: close
    Content-Type: image/jpeg
    Content-disposition: attachment; filename="MEDIA_ID.jpg"
    Date: Sun, 06 Jan 2013 10:20:18 GMT
    Cache-Control: no-cache, must-revalidate
    Content-Length: 339721
    curl -G "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=ACCESS_TOKEN&media_id=MEDIA_ID"

    错误情况下的返回JSON数据包示例如下（示例为无效媒体ID错误）：:

    {"errcode":40007,"errmsg":"invalid media_id"}
     */
    const MEDIA_DOWNLOAD_URL = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";


    /**
     * 创建自定义菜单的接口
     * http请求方式：POST（请使用https协议）
     */
    const MENU_CREATE_URL = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s";

    /**
     * 删除自定义菜单的接口
     *
     * http请求方式：GET
     */
    const MENU_DELETE_URL = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s";

    /**
     * 查询自定义菜单的接口
     *
     * http请求方式：GET
     */
    const MENU_QUERY_URL = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s";

    /**
     * 获取用户基本信息（包括UnionID机制）
     * http请求方式：GET
     * 返回结果：
     * json串
    {
    "subscribe": 1,
    "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
    "nickname": "Band",
    "sex": 1,
    "language": "zh_CN",
    "city": "广州",
    "province": "广东",
    "country": "中国",
    "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
    "subscribe_time": 1382694957,
    "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
    "remark": "",
    "groupid": 0
    }
     *
     */
    const USER_INFO_GET_URL = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";

    /**
     * 当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单、订阅事件、扫描二维码事件、支付成功事件、用户维权），
     * 微信将会把消息数据推送给开发者，开发者在一段时间内（目前修改为48小时）可以调用客服消息接口，
     * 通过POST一个JSON数据包来发送消息给普通用户，在48小时内不限制发送次数。
     * 此接口主要用于客服等有人工消息处理环节的功能，方便开发者为用户提供更加优质的服务。
     * POST
     */
    const SEND_CUSTOM_MESSAGE_URL = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s";

    /**
     * 模板消息仅用于公众号向用户发送重要的服务通知，只能用于符合其要求的服务场景中，如信用卡刷卡通知，商品购买成功通知等。
     * 不支持广告等营销类消息以及其它所有可能对用户造成骚扰的消息。
     *
     * POST
     */
    const SEND_TEMPLATE_MESSAGE_URL = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s";

    /**
     * 将一条长链接转成短链接。
     * 主要使用场景： 开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，
     * 将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
     * POST
     */
    const SHORT_URL_POST_URL = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token=%s";

    /**
     * 获取微信Js api ticket 的链接
     * GET
     */
    const JS_API_TICKET_GET_URL = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s";

    /**
     * 通过code换取网页授权access_token
     *
     * 参数	 是否必须	  说明
     * appid	是	公众号的唯一标识
     * secret	是	公众号的appsecret
     * code	    是	填写第一步获取的code参数
     *
     * 返回值：
     * {
     *   "access_token":"ACCESS_TOKEN",
     *   "expires_in":7200,
     *   "refresh_token":"REFRESH_TOKEN",
     *   "openid":"OPENID",
     *   "scope":"SCOPE",
     *   "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     */
    const OAUTH_ACCESS_TOKEN_GET_URL = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code";


    private static function isError($result)
    {
        if($result['errcode'] !== null){
            if($result['errcode'] == 0) {
                return false;
            } else {
                return true;
            }

        }else {
            return false;
        }

    }

    private static function getErrMessage($errCode, $errMsg = '') {
        $errMessages = array(
            '-1' => ' 系统繁忙',
            '40001' => ' 获取access_token时AppSecret错误，或者access_token无效',
            '40002' => ' 不合法的凭证类型',
            '40003' => ' 不合法的OpenID',
            '40004' => ' 不合法的媒体文件类型',
            '40005' => ' 不合法的文件类型',
            '40006' => ' 不合法的文件大小',
            '40007' => ' 不合法的媒体文件id',
            '40008' => ' 不合法的消息类型',
            '40009' => ' 不合法的图片文件大小',
            '40010' => ' 不合法的语音文件大小',
            '40011' => ' 不合法的视频文件大小',
            '40012' => ' 不合法的缩略图文件大小',
            '40013' => ' 不合法的APPID',
            '40014' => ' 不合法的access_token',
            '40015' => ' 不合法的菜单类型',
            '40016' => ' 不合法的按钮个数',
            '40017' => ' 不合法的按钮个数',
            '40018' => ' 不合法的按钮名字长度',
            '40019' => ' 不合法的按钮KEY长度',
            '40020' => ' 不合法的按钮URL长度',
            '40021' => ' 不合法的菜单版本号',
            '40022' => ' 不合法的子菜单级数',
            '40023' => ' 不合法的子菜单按钮个数',
            '40024' => ' 不合法的子菜单按钮类型',
            '40025' => ' 不合法的子菜单按钮名字长度',
            '40026' => ' 不合法的子菜单按钮KEY长度',
            '40027' => ' 不合法的子菜单按钮URL长度',
            '40028' => ' 不合法的自定义菜单使用用户',
            '40029' => ' 不合法的oauth_code',
            '40030' => ' 不合法的refresh_token',
            '40031' => ' 不合法的openid列表',
            '40032' => ' 不合法的openid列表长度',
            '40033' => ' 不合法的请求字符，不能包含\uxxxx格式的字符',
            '40035' => ' 不合法的参数',
            '40038' => ' 不合法的请求格式',
            '40039' => ' 不合法的URL长度',
            '40050' => ' 不合法的分组id',
            '40051' => ' 分组名字不合法',
            '40052' => ' Action Name不合法',
            '41001' => ' 缺少access_token参数',
            '41002' => ' 缺少appid参数',
            '41003' => ' 缺少refresh_token参数',
            '41004' => ' 缺少secret参数',
            '41005' => ' 缺少多媒体文件数据',
            '41006' => ' 缺少media_id参数',
            '41007' => ' 缺少子菜单数据',
            '41008' => ' 缺少oauth code',
            '41009' => ' 缺少openid',
            '42001' => ' access_token超时',
            '42002' => ' refresh_token超时',
            '42003' => ' oauth_code超时',
            '43001' => ' 需要GET请求',
            '43002' => ' 需要POST请求',
            '43003' => ' 需要HTTPS请求',
            '43004' => ' 需要接收者关注',
            '43005' => ' 需要好友关系',
            '44001' => ' 多媒体文件为空',
            '44002' => ' POST的数据包为空',
            '44003' => ' 图文消息内容为空',
            '44004' => ' 文本消息内容为空',
            '45001' => ' 多媒体文件大小超过限制',
            '45002' => ' 消息内容超过限制',
            '45003' => ' 标题字段超过限制',
            '45004' => ' 描述字段超过限制',
            '45005' => ' 链接字段超过限制',
            '45006' => ' 图片链接字段超过限制',
            '45007' => ' 语音播放时间超过限制',
            '45008' => ' 图文消息超过限制',
            '45009' => ' 接口调用超过限制',
            '45010' => ' 创建菜单个数超过限制',
            '45015' => ' 回复时间超过限制',
            '45016' => ' 系统分组，不允许修改',
            '45017' => ' 分组名字过长',
            '45018' => ' 分组数量超过上限',
            '46001' => ' 不存在媒体数据',
            '46002' => ' 不存在的菜单版本',
            '46003' => ' 不存在的菜单数据',
            '46004' => ' 不存在的用户',
            '47001' => ' 解析JSON/XML内容错误',
            '48001' => ' api功能未授权',
            '50001' => ' 用户未授权该api',
        );

        if(isset($errMessages[$errCode])){
            return trim($errMessages[$errCode]);
        } else {
            if (!empty($errMsg)) {
                return $errMsg;
            }
            return "未知错误：errcode:[$errCode]";
        }
    }

    /**
     * 获取永久二维码的Ticket
     * @param $accessToken
     * @param $sceneId
     * @return mixed 不出异常的话，返回获取的Ticket
     * @throws WxException
     */
    public static function getQrLimitSceneTicket($accessToken, $sceneId){
        if ($sceneId > 100000 || $sceneId <= 0) {
            throw new WxException('永久二维码只支持1--100000');
        }

        $params = sprintf('{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": %u}}}', $sceneId);

//        Yii::log("The limit qr post params is [$params]", 'warning');

        $url = sprintf(self::QR_TICKET_CREATE_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, $params, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result['ticket'];
        }

    }


    public static function getAccessToken($appId, $appSecret)
    {
        $url = sprintf(self::ACCESS_TOKEN_GET_URL, $appId, $appSecret);

        WxLogWriter::trace(__METHOD__ . $url);
        $rawResult = HttpFunction::callHttp($url, array(), 'GET');
        WxLogWriter::trace(__METHOD__ . $rawResult);

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']));
        } else {
            return $result['access_token'];
        }

    }

    public static function getQrImage($ticket){
        $url = sprintf(self::QR_IMAGE_GET_URL, $ticket);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        return $rawResult;
//        $result = FastJSON::decode($rawResult);
//
//        if(self::isError($result)) {
//            throw new WxException(self::getErrMessage($result['errcode']));
//        } else {
//            return $rawResult;
//        }

    }

    public static function getQrTempSceneTicket($accessToken, $sceneId, $expires = 1800)
    {
        if ($sceneId <= 100000 && $sceneId > 0) {
            throw new WxException('临时二维码须大于100000');
        }

        $params = sprintf('{"expire_seconds": %u, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": %u}}}', $expires, $sceneId);

        $url = sprintf(self::QR_TICKET_CREATE_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, $params, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result['ticket'];
        }

    }

    public static function getQrImageUrl($ticket)
    {
        return sprintf(self::QR_IMAGE_GET_URL, $ticket);
    }

    public static function deleteMenu($accessToken)
    {
        $url = sprintf(self::MENU_DELETE_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result['errcode'];
        }

    }

    public static function createMenu($accessToken, $menuJson)
    {
        $url = sprintf(self::MENU_CREATE_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, $menuJson, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result['errcode'];
        }

    }

    public static function getUserInfo($accessToken, $openId)
    {
        $url = sprintf(self::USER_INFO_GET_URL, $accessToken, $openId);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result;
        }

    }

    public static function sendCustomMessage($accessToken, $sendContent)
    {
        $url = sprintf(self::SEND_CUSTOM_MESSAGE_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, $sendContent, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result;
        }
    }

    /**
     * 通过调用微信的shorturl接口，将长链接转成短链接。
     *
     * @param $accessToken
     * @param $longUrl
     * @return mixed
     * @throws WxException
     */
    public static function shortUrl($accessToken, $longUrl){
        $url = sprintf(self::SHORT_URL_POST_URL, $accessToken);

        $sendContent = array(
            'action' => 'long2short',
            'long_url' => $longUrl,
        );

        $rawResult = HttpFunction::callHttp($url, $sendContent, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode'], $result['errmsg']) );
        } else {
            return $result['short_url'];
        }

    }

    public static function downloadFile($accessToken, $mediaId) {
        $url = sprintf(self::MEDIA_DOWNLOAD_URL, $accessToken, $mediaId);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        //判断是否下载成功：
        try{
            //todo 判断结果类型：
            $result = FastJSON::decode($rawResult);

            if(self::isError($result)) {
                throw new WxException(self::getErrMessage($result['errcode'], $result['errmsg']) );
            }

        }  catch(Exception $ex) {
            throw $ex;
        }

        //因为是下载文件，所以直接返回结果：
        return $rawResult;

    }

    public static function getMenuSetting($accessToken)
    {
        $url = sprintf(self::MENU_QUERY_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result;
        }

    }

    public static function getJsApiTicket($accessToken)
    {
        $url = sprintf(self::JS_API_TICKET_GET_URL, $accessToken);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']));
        } else {
            return $result['ticket'];
        }
    }

    public static function sendTemplateMessage($accessToken, $sendContent)
    {
        $url = sprintf(self::SEND_TEMPLATE_MESSAGE_URL, $accessToken);

        WxLogWriter::trace('Template message sending at' . date('Y-m-d H:i:s'));
        $rawResult = HttpFunction::callHttp($url, $sendContent, 'POST');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            WxLogWriter::trace('Template message sent at' . date('Y-m-d H:i:s') . ' and the msgId is ' . $result['msgid']);
            return $result;
        }
    }

    /**
     * @param GhAccessToken $accessInfo
     * @param $code
     * @return array|bool|float|int|mixed|null|stdClass|string
     * @throws WxException
     */
    public static function getPageAuthAccessTokenInfo($accessInfo, $code){
        $url = sprintf(self::OAUTH_ACCESS_TOKEN_GET_URL, $accessInfo->appId, $accessInfo->appSecret, $code);

        $rawResult = HttpFunction::callHttp($url, array(), 'GET');

        $result = FastJSON::decode($rawResult);

        if(self::isError($result)) {
            throw new WxException(self::getErrMessage($result['errcode']) );
        } else {
            return $result;
        }

    }
}
