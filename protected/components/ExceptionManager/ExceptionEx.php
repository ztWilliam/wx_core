<?php
/**
 * 自定义异常管理
 */
class ExceptionEx extends Exception{


    public function __construct($errorMsg){

        parent::__construct($errorMsg);
    }

}

?>