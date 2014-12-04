<?php
/*
* 初始化
* 自动加载library类库
* 自动加载extends扩展类库
* 增加配置文件目录
* 常量定义
*/
error_reporting(~E_NOTICE);
require_once dirname(__FILE__).'/library/Loader.class.php';
Loader::addPath(dirname(__FILE__).'/extends/');
Loader::autoload();

//加载配置
Config::addPath(dirname(dirname(__FILE__)).'/source/config/');

//路径初始化
$path = trim(trim(dirname(dirname($_SERVER['SCRIPT_NAME'])),'/'),"\\");
$path = empty($path)? '' : '/'.$path;

//主页URL
define('BASEURL',$path);

//默认为当前项目根目录，如有CDN则配置为CDN地址
define('CDN',$path.'/source');

//apps目录
define('APPPATH',dirname(dirname(__FILE__)).'/apps');

//Apache自定义SERVER信息
//用于保持本地代码与服务器代码一致性（如未配置可忽略）
if(isset($_SERVER['IS_DEV'])){
	define('IS_DEV',$_SERVER['IS_DEV']);
}else{
	define('IS_DEV',1);
}

//接口请求URL
if(empty($_SERVER['LOCAL_API_URL'])){
	define('APIBASEURL',"http://{$_SERVER['HTTP_HOST']}".BASEURL.'/api');
}else{
	define('APIBASEURL',$_SERVER['LOCAL_API_URL']);
}

//db config
if(empty($_SERVER['DB_HOST'])){
	define('DB_HOST','127.0.0.1');
}else{
	define('DB_HOST',$_SERVER['DB_HOST']);
}
if(empty($_SERVER['DB_PORT'])){
	define('DB_PORT','3306');
}else{
	define('DB_PORT',$_SERVER['DB_PORT']);
}
if(empty($_SERVER['DB_USER'])){
	define('DB_USER','root');
}else{
	define('DB_USER',$_SERVER['DB_USER']);
}
if(empty($_SERVER['DB_PASS'])){
	define('DB_PASS','');
}else{
	define('DB_PASS',$_SERVER['DB_PASS']);
}

//memcache config
if(empty($_SERVER['MEM_HOST'])){
	define('MEM_HOST','127.0.0.1');
}else{
	define('MEM_HOST',$_SERVER['MEM_HOST']);
}
if(empty($_SERVER['MEM_PORT'])){
	define('MEM_PORT','11211');
}else{
	define('MEM_PORT',$_SERVER['MEM_PORT']);
}

//Mongo config
define('MONGODB_HOST', $_SERVER['MONGO_DB_HOST']);
define('MONGODB_PORT', $_SERVER['MONGO_DB_PORT']);
define('MONGODB_USER', $_SERVER['MONGO_DB_USER']);
define('MONGODB_PWD', $_SERVER['MONGO_DB_PASS']);

//-----------------全局函数---------------------
//解决JSON中文Unicode
function jsonEncode($arr){
    $json = json_encode($arr);
    return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $json);
}

//获取HTTP请求头信息
function getHeadersString(){
	$header = getallheaders();
	$string = '';
	foreach ($header as $name => $value) {
		$string .= "{$name}: {$value}\r\n";
	}
	$string .= "URL: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\r\n";
	$string .= "POST: ".urldecode(http_build_query($_POST))."\r\n";
	return $string;
}
