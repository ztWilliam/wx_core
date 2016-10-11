<?php
if(isset($_POST['PHPSESSID'])) $_COOKIE['PHPSESSID'] = $_POST['PHPSESSID'];
error_reporting(E_ALL ^ E_NOTICE);

/**
 * 可选项development,test,production,
 */
defined('APP_ENV') or define('APP_ENV','development');

// change the following paths if necessary
if (APP_ENV == 'production') {
    $yii=dirname(__FILE__).'/../yii/framework/yiilite.php';
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',1);
} else {
    $yii=dirname(__FILE__).'/../yii/framework/yii.php';
    // remove the following lines when in production mode
    defined('YII_DEBUG') or define('YII_DEBUG',true);
    // specify how many levels of call stack should be shown in each log message
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
}
//$config=dirname(__FILE__).'/protected/config/'.APP_ENV.'.php';

/*******************************************/
/*为实现swfupload多文件上传，特加以下代码：*/
//if (isset($_POST["PHPSESSID"])) 
//{
//	session_id($_POST["PHPSESSID"]);
//} else if (isset($_GET["PHPSESSID"])) 
//{
//	session_id($_GET["PHPSESSID"]);
//}
/********************************************/
$server = explode('.', implode('.', array_reverse(explode(':', rtrim($_SERVER['HTTP_HOST'], '.')))));
$config = array();
for ($j = count($server); $j > 0; $j--) {
    $file = dirname(__FILE__).'/protected/config/' . implode('.', array_slice($server, -$j))  . '.php';
    if (file_exists($file)) {
        $config = dirname(__FILE__).'/protected/config/'.APP_ENV.'.php';
        break;    }}
if (empty($config)) {
    $config = dirname(__FILE__).'/protected/config/'.APP_ENV.'.php';
}

require_once($yii);

// use third party components
// MAILER
//require_once(dirname(__FILE__).'/protected/components/mailer/class.phpmailer.php');

Yii::createWebApplication($config)->run();