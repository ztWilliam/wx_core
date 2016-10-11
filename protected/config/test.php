<?php
 
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
        'components'=>array(
            'fixture'=>array(
                'class'=>'system.test.CDbFixtureManager',
            ),

            'db' => array(
                'connectionString' => 'mysql:host=localhost;dbname=wx_core_test',

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
        ),
	)
);
