<?php
//初始化
require_once '../core/boot.inc.php';

/*
* 获取请求模块
* m 为模块
* c 为控制器
* t 为控制器内自定义方法
* 将请求的模块控制器加载进来
*/
$_GET['m'] = empty($_GET['m'])? 'home' : addslashes($_GET['m']);
$_GET['c'] = empty($_GET['c'])? 'index' : addslashes($_GET['c']);
$file = dirname(__FILE__)."/{$_GET['m']}/";

if( ! is_dir($file) || !file_exists($file.$_GET['c'].'.php')){
    exit('404');
}

require_once $file.$_GET['c'].'.php';
