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
Config::addPath(dirname(__FILE__).'/config/');
$path = trim(dirname($_SERVER['SCRIPT_NAME']),'/');
$path = empty($path)? '' : '/'.$path;

define('BASEURL',$path);
define('CDN',$path); // 默认为当前根目录，如有CDN则配置为服务器域名如http://www.example.com
define('APPPATH',dirname(dirname(__FILE__)).'/apps');

// monitor-server config
/*
define(HTTP_URL, $_SERVER['HTTP_URL']);//monitor
define(AUTH_KEY, $_SERVER['AUTH_KEY']);
define(HTTP_URL_NOTIFY, $_SERVER['HTTP_URL_NOTIFY']);//notify
define(AUTH_KEY_NOTIFY, $_SERVER['AUTH_KEY_NOTIFY']);
define(YYPORT_M14, $_SERVER['YYPORT_M14']);
*/
//Apache自定义SERVER信息，用于保持SVN代码与服务器代码一致性（如未配置可忽略）
if(isset($_SERVER['IS_DEV'])){
	define('IS_DEV',$_SERVER['IS_DEV']);
}else{
	define('IS_DEV',1);
}
if(empty($_SERVER['LOCAL_API_URL'])){
	define('APIBASEURL',"http://{$_SERVER['HTTP_HOST']}".BASEURL.'/api');
}else{
	define('APIBASEURL',$_SERVER['LOCAL_API_URL']);
}
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

//全局函数
function jsonEncode($arr){
    $json = json_encode($arr);
    return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $json);
}

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
