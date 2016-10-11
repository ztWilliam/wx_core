<?php
/**
 * 
 * User: william
 * Date: 15-10-11
 * Time: 下午4:45
 */

class WxOAuth {
    /**
     * @var GhAccessToken
     */
    private $accessInfo = null;

    /**
     * @var string
     */
    private $ghId = null;

    /**
     * 用于由微信进行OAuth用户授权并跳转的链接：
     * appid : 对应公众号的appId
     * redirect_uri : 拟跳转的url，并用html_encode转
     * state : 公众号的id
     */
    const WX_OAUTH_BASE_REDIRECT_URL_TEMPLATE = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect";
    const WX_OAUTH_USER_INFO_REDIRECT_URL_TEMPLATE = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s#wechat_redirect";

    /**
     * @param string $ghId
     */
    public function __construct($ghId){
        $this->ghId = $ghId;
    }

    public function baseScopeUrl($redirectUrl) {

        $this->refreshAccessInfo();

        $encodedUrl = urlencode($redirectUrl);
        $url = sprintf(self::WX_OAUTH_BASE_REDIRECT_URL_TEMPLATE, $this->accessInfo->appId, $encodedUrl, $this->accessInfo->ghId );

        return $url;
    }

    public function userInfoScopeUrl($redirectUrl) {
        $this->refreshAccessInfo();

        $encodedUrl = urlencode($redirectUrl);
        $url = sprintf(self::WX_OAUTH_USER_INFO_REDIRECT_URL_TEMPLATE, $this->accessInfo->appId, $encodedUrl, $this->accessInfo->ghId );

        return $url;
    }

    public function getPageAccessToken($code) {
        $this->refreshAccessInfo();

        return WxCgiCaller::getPageAuthAccessTokenInfo($this->accessInfo, $code);
    }

    private function refreshAccessInfo()
    {
        if ((!isset($this->accessInfo)) || ($this->ghId != $this->accessInfo->ghId)) {
            //根据$ghInfo信息获取accessToken
            $theAccessInfo = GhAccessToken::findAccessObj($this->ghId);

            if(!isset($theAccessInfo)){
                throw new Exception('公众号授权信息无效');
            }

            $this->accessInfo = $theAccessInfo;
        }
    }

}