<?php
$lifetime = IS_DEV? 1 : 3600;
return array(
    'web' => array(
		'path' => dirname(dirname(dirname(__FILE__))).'/apps/web/'.$_GET['m'].'/tpl', //指定模板文件存放目录
		'compile' => dirname(dirname(dirname(__FILE__))).'/source/temp/web/'.$_GET['m'].'/tpl', //指定缓存文件存放目录
		'lifetime' => $lifetime, //缓存生命周期(秒)，为 0 表示永久
	),
	
	'admin' => array(
		'path' => dirname(dirname(dirname(__FILE__))).'/apps/admin/'.$_GET['m'].'/tpl', //指定模板文件存放目录
		'compile' => dirname(dirname(dirname(__FILE__))).'/source/temp/admin/'.$_GET['m'].'/tpl', //指定缓存文件存放目录
		'lifetime' => $lifetime, //缓存生命周期(秒)，为 0 表示永久
	),

	'startup' => array(
		'path' => dirname(dirname(dirname(__FILE__))).'/startup/'.$_GET['m'].'/tpl', //指定模板文件存放目录
		'compile' => dirname(dirname(dirname(__FILE__))).'/source/temp/startup/'.$_GET['m'].'/tpl', //指定缓存文件存放目录
		'lifetime' => $lifetime, //缓存生命周期(秒)，为 0 表示永久
	),
);