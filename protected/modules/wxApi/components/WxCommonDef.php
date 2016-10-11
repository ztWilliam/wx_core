<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-5
 * Time: 下午4:25
 * To change this template use File | Settings | File Templates.
 */

class WxCommonDef {

    /**
     * （重要！！）
     * 跟在后面的，是需要在部署时修改的定义项：
     */

    //是否启用调试模式，如果为true，会在关键功能执行时，记录日志：
    //
    const DEBUG_MODE = true;

    //////////////////* 分割线 *////////////////////

    /**
     * 微信消息相关的模板：
     */
    const COMMON_TPL = "<xml>
                       <ToUserName><![CDATA[%s]]></ToUserName>
                       <FromUserName><![CDATA[%s]]></FromUserName>
                       <CreateTime>%s</CreateTime>
                        %s
                       </xml>";

    const  TEXT_CONTENT_TPL = "<MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>";

    const NEWS_LIST_TPL = "<MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>%s</Articles>";

    const NEWS_LIST_ITEM_TPL = "<item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>";

    const IMAGE_CONTENT_TPL = "<MsgType><![CDATA[image]]></MsgType>
                    <Image>
                    <MediaId><![CDATA[%s]]></MediaId>
                    </Image>";

    const VOICE_CONTENT_TPL = "<MsgType><![CDATA[voice]]></MsgType>
                    <Voice>
                    <MediaId><![CDATA[%s]]></MediaId>
                    </Voice>";

    const VIDEO_CONTENT_TPL = "<MsgType><![CDATA[video]]></MsgType>
                    <Video>
                    <MediaId><![CDATA[%s]]></MediaId>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    </Video> ";

    const MUSIC_CONTENT_TPL = "<MsgType><![CDATA[music]]></MsgType>
                    <Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
                    </Music>";


    //////////////////* 分割线 *////////////////////

    /**
     * 内部使用的常量定义
     * 不要轻易改！（除非你知道在做什么）
     */
    //handler的类型定义
    const HANDLER_TYPE_URL = 'url';
    const HANDLER_TYPE_CLASS = 'class';
    const HANDLER_TYPE_NONE = 'none';

    //云存储支持的文件类型：
    const FILE_TYPE_IMAGE = 'image';
    const FILE_TYPE_VOICE = 'voice';
    const FILE_TYPE_VIDEO = 'video';

    //对所有未定义字段的初值：
    const FIELD_NOT_DEFINED = 'none';


}