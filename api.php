<?php
/*
* 初始化
* 自动加载library类库
* 自动加载extends扩展类库
* 增加配置文件目录
*/
error_reporting(~E_NOTICE);
require_once 'core/library/Loader.class.php';
Loader::addPath(dirname(__FILE__).'/core/extends/');
Loader::autoload();
Config::addPath(dirname(__FILE__).'/core/config/');

/*
* 获取请求模块
* m 为模块
* c 为控制器
* t 为控制器内自定义方法
* 将请求的模块控制器加载进来
*/
$m = empty($_GET['m'])? 'mgr' : addslashes($_GET['m']);
$c = empty($_GET['c'])? 'index' : addslashes($_GET['c']);
if(empty($_GET['t'])) throw new Exception("请求的接口不存在",301);
$t = $_GET['t'];
$file = "apps/api/{$m}/";
if( ! is_dir($file)){
    throw new Exception("请求的模块不存在",302);
}
if( ! file_exists($file.$c.'.php')){
    throw new Exception("请求的模块文件不存在",303);
}
require_once $file.$c.'.php';

//全局函数
function jsonEncode($arr){
    $json = json_encode($arr);
    return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $json);
}

/*
* 执行请求的方法
* 并将返回的数据格式化输出
* 如果遇到异常格式化错误信息输出
*/
try{
	if(strpos($t,'sig_') !== false){
		$usersig = new UserSig();
		$httpquery = new HttpQuery();
		$extparam = json_decode($httpquery->query('extparam'),true);
		$usersig->verifySig($extparam['sig']);
	}
    $contrl = new $c();
    $data = $contrl->$t();
	$log = Logs::connect(Config::get('logs.conf/success_log'));
	$log->write("#POST: ".json_encode($_POST)."\r\n#GET: ".json_encode($_GET)."\r\n#RST: ".json_encode(array('code'=>100,'data'=>$data)));
    echo jsonEncode(array('code'=>100,'data'=>$data));
}catch(PDOException $e){
	$log = Logs::connect(Config::get('logs.conf/mysql_log'));
	$log->write($e->getTraceAsString()."\r\n#POST: ".json_encode($_POST)."\r\n#GET: ".json_encode($_GET));
    echo jsonEncode(array('code'=>$e->getCode(),'desc'=>$e->getMessage()));
}catch(Exception $e){
	$log = Logs::connect(Config::get('logs.conf/php_log'));
	$log->write($e->getTraceAsString()."\r\n#POST: ".json_encode($_POST)."\r\n#GET: ".json_encode($_GET));
    echo jsonEncode(array('code'=>$e->getCode(),'desc'=>$e->getMessage()));
}
