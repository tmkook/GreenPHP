<?php
//初始化
require_once dirname(dirname(__FILE__)).'/core/boot.inc.php';

try{
	/*
	* 获取请求模块
	* m 为模块
	* c 为控制器
	* t 为控制器内自定义方法
	* 将请求的模块控制器加载进来
	*/
	if(empty($_GET['m'])) throw new Exception("请求的模块不存在",301);
	if(empty($_GET['c'])) throw new Exception("请求的接口不存在",302);
	if(empty($_GET['t'])) throw new Exception("请求的方法不存在",303);
	
	$m = addslashes($_GET['m']);
	$c = addslashes($_GET['c']);
	$t = addslashes($_GET['t']);

	$file = APPPATH."/api/{$m}/";
	if( ! is_dir($file)){
		throw new Exception("请求的模块不存在",304);
	}
	if( ! file_exists($file.$c.'.php')){
		throw new Exception("请求的模块文件不存在",305);
	}
	require_once $file.$c.'.php';

	/*
	* 执行请求的方法
	* 并将返回的数据格式化输出
	* 如果遇到异常格式化错误信息输出
	*/
	$extparam = HttpQuery::extparam();
	$param = HttpQuery::param();
	if(strpos($t,'sig_') !== false && isset($param['uin']) && $param['uin'] >= 200000){ //用户id大于200000时验证sig
		HttpQuery::isSigin($extparam['sig']);
	}
    $contrl = new $c();
    $data = $contrl->$t();
    //记录请求日志
	$log = Logs::connect(Config::get('logs.conf/success_log'));
	$log->write(getHeadersString()."Rst: ".jsonEncode($data));
    echo jsonEncode(array('code'=>100,'data'=>$data));
}catch(PDOException $e){
	//记录DB异常
	$log = Logs::connect(Config::get('logs.conf/mysql_log'));
	$log->write(getHeadersString());
    echo jsonEncode(array('code'=>$e->getCode(),'desc'=>$e->getMessage()));
}catch(Exception $e){
	//记录脚本异常
	$log = Logs::connect(Config::get('logs.conf/php_log'));
	$log->write(getHeadersString()."Msg: ".$e->getMessage().$e->getCode());
	$msg = $e->getMessage();
	//$_SERVER['API_LANGUAGE'] = 'en';
	if(isset($_SERVER['API_LANGUAGE'])){
		$lang = Config::get($_SERVER['API_LANGUAGE'].'.conf');
		if(isset($lang[$msg])){
			$msg = $lang[$msg];
		}
	}
    echo jsonEncode(array('code'=>$e->getCode(),'desc'=>$msg));
}
