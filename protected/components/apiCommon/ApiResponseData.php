<?php

/**
 * 用于Api的返回数据格式
 */

class ApiResponseData {

    var $responseCode;// int
    var $responseDesc;//string
    var $data;//array
    var $responseType;  //string 待扩展

    public function __construct($rCode, $rDesc, $dataArr, $rType = '') {
        $this->responseCode = $rCode;
        $this->responseDesc = $rDesc;
        $this->data = $dataArr;
        $this->responseType = $rType;
    }
}

?>
