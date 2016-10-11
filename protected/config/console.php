<?php
 
// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Wx_Core console',
	// application components
    'import'=>array(
        'application.commands.*',
        'application.models.*',
        'application.extensions.phpass.*',
        'application.components.*',
        'application.components.common.*',
        'application.components.ExceptionManager.*',
        'application.components.textUtil.*',
        'application.components.apiCommon.*',
    ),

    'commandMap'=>array(

    ),

    'modules' => array(
        'statistic' => array(

        ),
    ),

	'components'=>array(
        'db'=>array(
            'class'=>'CDbConnection',

            'connectionString'=>'mysql:host=localhost;dbname=wx_core_dev',
            'emulatePrepare'=>true,
            'username' => 'coredba',
            'password' => 'cOrEdbA',

            'charset'=>'utf8',
            'tablePrefix'=>'',
            'enableProfiling'=>true,
            'schemaCachingDuration'=>0,
            'enableParamLogging'=>true,
        ),

		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		*/
	),
	'params' => array(
	),
);