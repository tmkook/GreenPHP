<?php
return array(
	'php_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=pomelo",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
		'domain'   => 'exception_',
		'name'     => '程序异常日志',
	),
	'mysql_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=pomelo",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
		'domain'   => 'pdo_',
		'name'     => '数据库异常日志',
	),
	'success_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=pomelo",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
		'domain'   => 'success_',
		'name'     => '接口请求日志',
	),
	'admin_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=pomelo",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
		'domain'   => 'admin_action_',
		'name'     => '后台操作日志',
	),
	/*
	'filelog' => array(
		'driver' => 'FileLog', //缓存驱动
		'path'   => dirname(__FILE__).'/', //缓存存放位置
		'domain' => 'exception', //缓存目录
	),
	*/
);