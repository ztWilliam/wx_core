<?php
/**
 * 
 * User: william
 * Date: 15-3-18
 * Time: 上午9:47
 */

class ApiCommonDef {

    const CONDITION_NO_LIMIT = -1;      //表示某个条件为“不限制”的取值。

    const TIME_NOT_SET = '1900-01-01 00:00:00';     //对于非null的时间属性，表示未设置时间时的具体取值
    const TIME_NOT_SET_DISPLAY = '-';     //对于非null的时间属性，表示未设置时间时的具体显示内容

    const OPERATION_RESULT_SUCCESS = 0;
    const OPERATION_RESULT_FAILED = 1;

}