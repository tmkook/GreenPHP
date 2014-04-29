<?php

return array(
    'web' => array(
		'path' => dirname(dirname(dirname(__FILE__))).'/apps/web/', //指定模板文件存放目录
		'compile' => dirname(dirname(dirname(__FILE__))).'/temp/web/', //指定缓存文件存放目录
		'lifetime' => 1, //缓存生命周期(秒)，为 0 表示永久
	),
	'admin' => array(
		'path' => dirname(dirname(dirname(__FILE__))).'/apps/admin/', //指定模板文件存放目录
		'compile' => dirname(dirname(dirname(__FILE__))).'/temp/admin/', //指定缓存文件存放目录
		'lifetime' => 1, //缓存生命周期(秒)，为 0 表示永久
	),

);