<?php
/**
 * Description: 
 * put the description here.
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 12-8-18
 * Time: 下午1:54
 * 
 */
 
class LogWriter {

    public static function logModelSaveError($model, $class_method = '', $params = array())
    {
        $errStr = '';
        if($model instanceof CActiveRecord)
        {
            foreach($model->getErrors() as $error)
            {
                $errStr .= $error[0];
            }

            $logStr = $model->tableName() . "保存失败: " . $errStr . ".";

            if(!empty($class_method))
                $logStr .= "发生于：" . $class_method . ".";

            if(isset($params) )
            {
                foreach($params as $paramKey => $paramValue)
                {
                    $logStr .= "参数[". $paramKey ."]的值为[". $paramValue ."]；";
                }

            }

            Yii::log($logStr, 'error');
        }

    }
}
