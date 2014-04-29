<?php
return array(
	'php_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=127.0.0.1;port=3306;dbname=greenphp",
		'username' => 'root',
		'password' => '',
		'charset'  => 'UTF8',
		'domain'   => 'exception_'
	),
	'mysql_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=127.0.0.1;port=3306;dbname=greenphp",
		'username' => 'root',
		'password' => '',
		'charset'  => 'UTF8',
		'domain'   => 'pdo_'
	),
	'success_log' => array(
		'driver' => 'DatabaseLog', //缓存驱动
		'dsn'  =>  "mysql:host=127.0.0.1;port=3306;dbname=greenphp",
		'username' => 'root',
		'password' => '',
		'charset'  => 'UTF8',
		'domain'   => 'success_'
	),
	'filelog' => array(
		'driver' => 'FileLog', //缓存驱动
		'path'   => dirname(__FILE__).'/', //缓存存放位置
		'domain' => 'exception', //缓存目录
	),
);