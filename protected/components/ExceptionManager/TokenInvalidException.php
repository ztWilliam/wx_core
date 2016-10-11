<?php
/**
 * Created by JetBrains PhpStorm.
 * User: zhengtuoqingdao
 * Date: 12-7-26
 * Time: 下午6:05
 * To change this template use File | Settings | File Templates.
 */
class TokenInvalidException extends ExceptionEx
{
    public function __construct($errorMsg){

        parent::__construct($errorMsg);
    }
}
