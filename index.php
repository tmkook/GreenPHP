<?php
/*
* 初始化
* 自动加载library类库
* 自动加载extends扩展类库
* 增加配置文件目录
*/
require_once 'core/library/Loader.class.php';
Loader::addPath(dirname(__FILE__).'/core/extends/');
Loader::autoload();
Config::addPath(dirname(__FILE__).'/core/config/');

$path = trim(dirname($_SERVER['SCRIPT_NAME']),'/');
$path = empty($path)? '' : '/'.$path;
define('BASEURL',$path);

/*
* 获取请求模块
* m 为模块
* c 为控制器
* t 为控制器内自定义方法
* 将请求的模块控制器加载进来
*/
$m = empty($_GET['m'])? 'mgr' : addslashes($_GET['m']);
$c = empty($_GET['c'])? 'index' : addslashes($_GET['c']);
$file = "apps/web/{$m}/";
if( ! is_dir($file)){
    throw new Exception("请求的模块不存在",302);
}
if( ! file_exists($file.$c.'.php')){
    throw new Exception("请求的模块文件不存在",303);
}

require_once $file.$c.'.php';
