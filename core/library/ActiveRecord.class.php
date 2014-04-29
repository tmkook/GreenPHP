<?php
/**
* 数据库CRUD类
* 
* 如果不想使用CRUD可以使用 ActiveRecord::getPdo(); 方法获取PDO对象
*/
require_once 'fluentPDO/FluentPDO.php';

class ActiveRecord extends FluentPDO
{
    public function __construct($conf){
		$pdo = new PDO($conf['dsn'],$conf['username'],$conf['password'],
			array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES `{$conf['charset']}`",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			)
        );
        parent::__construct($pdo);
    }
}


