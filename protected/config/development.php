<?php
 
return CMap::mergeArray(
    require(dirname(__FILE__) . '/main.php'),
    array(
        'components' => array(

            'db' => array(
                'connectionString' => 'mysql:host=localhost;dbname=wx_core_dev',

                'class' => 'CDbConnection',

                'emulatePrepare' => true,
                'username' => 'coredba',
                'password' => 'cOrEdbA',

                'charset' => 'utf8',
                'tablePrefix' => '',
                'enableProfiling' => true,
                'schemaCachingDuration' => 0,
                'enableParamLogging' => true,
            ),

            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'error, warning, trace',
                    ),
                    array(
                        'class' => 'CProfileLogRoute',
                    ),
                ),
            ),

            'urlManager' => array(
                'rules' => array(
                    'gii' => 'gii',
                    'gii/<controller:\w+>' => 'gii/<controller>',
                    'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
                ),

            ),
        ),

        'modules' => array(
            // uncomment the following to enable the Gii tool
            'gii' => array(
                'class' => 'system.gii.GiiModule',
                'password' => 'sa',
                // If removed, Gii defaults to localhost only. Edit carefully to taste.
                'ipFilters' => array('127.0.0.1', '::1'),
            ),

            'wxApi' => array(
                /**
                 * 微信公众平台，用于接入开发模式的url根路径
                 */
                'url' => "http://localhost/wx_core/wxApi/weixin/reply",
            ),

            'statistic' => array(

            ),

        ),

        'params' => array(
        ),
    )
);
