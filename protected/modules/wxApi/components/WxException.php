<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-11
 * Time: 上午11:08
 * To change this template use File | Settings | File Templates.
 */
class WxException extends Exception{

    public function __construct($errorMsg){

        parent::__construct($errorMsg);
    }


    const ERR_MSG_TYPE_WRONG = 'Parameter type wrong, [%s] type expected.';    //需要嵌入正确的数据类型
    const ERR_MSG_MODEL_SAVE_ERROR = '%s model save failed, when %s. '; //参数：1、类名， 2、场景描述，通常可以是方法名
    const ERR_MSG_PARAMETER_REQUIRED = '%s parameter must be supplied. (Method: %s)'; //参数：1、参数名  2、方法名

}
