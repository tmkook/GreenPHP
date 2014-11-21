<?php
/**
* 数据库CRUD类
* 
* 如果不想使用CRUD可以使用 $db->getPdo(); 方法获取PDO对象
*/
require_once 'fluentPDO/FluentPDO.php';
class Database
{
	static protected $ars = array();
    static function connect($dsn,$pconn=false){
		$key = md5(json_encode($dsn).$pconn);
		if(!isset(self::$ars[$key])){
			$pdo = new PDO($dsn['dsn'],$dsn['username'],$dsn['password'],
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES `{$dsn['charset']}`",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_PERSISTENT => $pconn,
				)
			);
			self::$ars[$key] = new FluentPDO($pdo);
		}
		return self::$ars[$key];
    }
}
