<?php
define('STDERR',dirname(dirname(dirname(__FILE__))).'/temp/sql_queries.txt');

return array(

	'default' => array(
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=pomelo",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
	),
	
	'coolcar' => array(
		'dsn'  =>  "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=game_coolcar",
		'username' => DB_USER,
		'password' => DB_PASS,
		'charset'  => 'UTF8',
	),
);
