<?php

return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
    array(
        'components' => array(

            'db' => array(
                'connectionString' => 'mysql:host=localhost;dbname=wx_core',

                'class' => 'CDbConnection',

                'emulatePrepare' => true,
                'username' => '[db user]',
                'password' => '[db psd]',

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
                        'levels' => 'error, warning',
                    ),
                    array(
                        'class' => 'CProfileLogRoute',
                    ),
                ),
            ),

            'urlManager' => array(
                'rules' => array(
                ),

            ),
        ),

        'modules' => array(
            'wxApi' => array(
                /**
                 * 微信公众平台，用于接入开发模式的url根路径
                 */
                'url' => "[your url deployed in production server]",
            ),
            'statistic' => array(

            ),

        ),

        'params' => array(
        ),
    )
);

