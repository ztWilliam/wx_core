<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-27
 * Time: 下午9:41
 * To change this template use File | Settings | File Templates.
 */

class WxCommonFunction {

    public static function checkHandler($handler)
    {
        //判断是否以 http: 开头，若是，则说明是url，就不进行业务类的合法性检查了：
        $startWith = substr($handler, 0, 6);

        if($startWith == "http:/" || $startWith == "https:") {
            //既支持http的api，也支持https的api：
            return WxCommonDef::HANDLER_TYPE_URL;
        }

        $handlerObj = Yii::createComponent($handler);
        if(!isset($handlerObj)) {
            throw new WxException('业务处理类名无效');
        }

        return WxCommonDef::HANDLER_TYPE_CLASS;
    }

    /**
     * 调用事件处理的业务类或url api
     * 对于url类型的处理方法：采用post方式调用；
     * 对于业务类，须实现IWxEventHandler接口（本module下的components.listeners.IWxEventHandler接口）
     * @param $handler  string类型，业务处理类或url地址
     * @param $handlerType  string类型， url or class
     * @param $eventObj   对象， 微信事件的post信息，转化成的对象
     * @param string $parameters  json字符串，事件或场景注册时，预留的自定义参数
     * @return string  可以直接返回给微信服务器的消息字符串。
     * @throws Exception
     */
    public static function callEventHandler($handler, $handlerType, $eventObj, $parameters = "")
    {
        if($handlerType == WxCommonDef::HANDLER_TYPE_URL) {
            //
            $params = array(
                'eventObj' => FastJSON::encode($eventObj),
                'parameters' => $parameters,
            );

            WxLogWriter::trace('calling url : ' . $handler . ', parameters is : ' . FastJSON::encode($params));

            $result = HttpFunction::callHttp($handler, $params, 'POST');

            WxLogWriter::trace('called url : ' . $handler . ', result is : ' . $result);

        } elseif($handlerType == WxCommonDef::HANDLER_TYPE_CLASS) {
            //
            $handlerObj = Yii::createComponent($handler);
            if($handlerObj instanceof IWxEventHandler)
                $result = $handlerObj->handleEvent($eventObj, $parameters);
            else
                throw new Exception('无效的事件处理Handler实例（须实现IWxEventHandler接口）：' . $handler);
        } else {
            throw new Exception('无效的事件处理HandlerType :' . $handlerType );
        }

        return $result;
    }


    /**
     * 调用微信消息处理的业务类或url api
     * 对于url类型的处理方法：采用post方式调用；
     * 对于业务类，须实现IWxMessageListener接口（本module下的components.listeners.IWxMessageListener接口）
     * @param $handler
     * @param $handlerType
     * @param $msgObj
     * @param string $parameters
     * @return mixed|string
     * @throws Exception
     */
    public static function callListenerHandler($handler, $handlerType, $msgObj, $parameters = "")
    {
        if($handlerType == WxCommonDef::HANDLER_TYPE_URL) {

            $params = array(
                'msgObj' => FastJSON::encode($msgObj),
                'parameters' => $parameters,
            );

            $result = HttpFunction::callHttp($handler, $params, 'POST');

        } elseif($handlerType == WxCommonDef::HANDLER_TYPE_CLASS) {
            //
            $handlerObj = Yii::createComponent($handler);
            if($handlerObj instanceof IWxMessageListener)
                $result = $handlerObj->replyMessage($msgObj);
            else
                throw new Exception('无效的消息处理Handler实例（须实现IWxMessageListener接口）：' . $handler);
        } else {
            throw new Exception('无效的消息处理HandlerType :' . $handlerType );
        }

        return $result;
    }

    public static function echoTextMessage($toUserName, $fromUserName, $message)
    {
        $time = time();

        $content = sprintf(WxCommonDef::TEXT_CONTENT_TPL, $message);
        $resultStr = sprintf(WxCommonDef::COMMON_TPL, $toUserName, $fromUserName, $time, $content);

        return $resultStr;
    }

}