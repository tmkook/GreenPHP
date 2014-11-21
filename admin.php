<?php
//初始化
require_once 'core/boot.inc.php';
session_start();

/*
* 获取请求模块
* m 为模块
* c 为控制器
* t 为控制器内自定义方法
* 将请求的模块控制器加载进来
*/
$m = empty($_GET['m'])? 'mgr' : addslashes($_GET['m']);
$c = empty($_GET['c'])? 'index' : addslashes($_GET['c']);

if(empty($_SESSION['login_admin']) && $c != 'login'){
    header('location:'.BASEURL.'/admin/mgr/login');
}

if($_SESSION['login_admin']['access'] != 'all' && $m != 'mgr'){
	$access = $_SESSION['login_admin']['access'];
	if(!isset($access[$m]) || !in_array($c,$access[$m])){
		exit('权限不足');
	}
}

$file = APPPATH."/admin/{$m}/";

if( ! is_dir($file) ||  ! file_exists($file.$_GET['c'].'.php')){
    throw new Exception('你似乎迷路了 <a href="/">Click me</a>',404);
}

$nolog = array('login','welcome','index');
if( ! in_array($c,$nolog)){
	$log = Logs::connect(Config::get('logs.conf/admin_log'));
	$log->write(getHeadersString()."\r\nActionUser: {$_SESSION['login_admin']['username']}");
}
require_once $file.$c.'.php';