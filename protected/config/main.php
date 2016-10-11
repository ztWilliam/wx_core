<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'证拓-微信开发框架分享平台',

	'timezone'=>'PRC',

	'sourceLanguage'=>'en_us',
	'language'=>'zh_cn',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
        'application.extensions.phpass.*',
        'application.extensions.curl.*',
        'application.components.*',
        'application.components.common.*',
        'application.components.ExceptionManager.*',
        'application.components.textUtil.*',
        'application.components.apiCommon.*',
	),

	// application components
	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, info, trace',
					'maxFileSize'=>5000,
					'maxLogFiles'=>30,
				),
			),
		),

        //Yii::app()->session  可以引用到这里
        'session' => array(
            'class' => 'system.web.CDbHttpSession', // 标记使用 CDbHttpSession
            'connectionID' => 'db',           // 使用组件中哪个数据库连接
            'sessionTableName'=>'yiisession', // 表名字
            'autoCreateSessionTable'=>true,   // 自动创建session表
            'timeout'      => 1440,           // 设置闲置多久session超时 (默认是 1440 秒)
        ),

		'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName' => false,
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
		),

        'hasher'=>array (
            'class'=>'Phpass',
            'hashPortable'=>false,
            'hashCostLog2'=>8,
        ),

    ),

	'modules'=>array(
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
        'redis' => array(
            'host' => 'localhost',
            'port' => '6379',
            'password' => '9acf3a60a243497b:zhEng51tUo',
            'database' => 9,
        ),
	),
);