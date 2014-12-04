<?php
//对接口请求进行封装
//方便PHP请求自己的接口
class HttpQuery
{
	protected static $param;
	protected static $extparam;
	
	//验证签名
	public static function isSigin(){
		$param = self::param();
		$extparam = json_decode(self::query('extparam'),true);
		$usersig = new UserSig();
		$userinfo = $usersig->verifySig($extparam['sig']);
		if($userinfo['uin'] != $param['uin']){
			throw new Exception("签名不正确",201);
		}
		return $userinfo;
	}
	
	//API请求
	public static function api($api,$param,$ext=array()){
		$extparam = self::extparam();
		$extparam = json_encode(array_merge((array)$extparam,(array)$ext));
		if(is_array($param)) $param = json_encode($param);
		$query = array(
			'parameter'=>$param,
			'extparam' => $extparam,
		);
		$rst = Http::request(APIBASEURL.$api,$query,'');
		return (array)json_decode($rst,true);
	}
	
	//异步API请求
	public static function apiAsync($api,$param,$ext=array()){
		$extparam = self::extparam();
		$extparam = json_encode(array_merge((array)$extparam,(array)$ext));
		if(is_array($param)) $param = json_encode($param);
		$query = array(
			'parameter'=>$param,
			'extparam' => $extparam,
		);
		HttpAsync::request(APIBASEURL.$api,$query,'');
	}
		
	public static function get($key){
		return isset($_GET[$key])? $_GET[$key] : '';
	}
	
	public static function post($key){
		return isset($_POST[$key])? $_POST[$key] : '';
	}
	
	public static function query($key){
		if(isset($_POST[$key])){
			return $_POST[$key];
		}elseif(isset($_GET[$key])){
			return $_GET[$key];
		}else{
			return '';
		}
	}
	
	public static function param($key=''){
		$json = self::query('parameter');
		if(empty($json)) return '';
		self::$param = json_decode($json,true);
		if(empty($key)) return self::$param;
		return isset(self::$param[$key])? self::$param[$key] : '';
	}
	
	public static function extparam($key=''){
		$json = self::query('extparam');
		if(empty($json)) return '';
		self::$param = json_decode($json,true);
		if(empty($key)) return self::$param;
		return isset(self::$param[$key])? self::$param[$key] : '';
	}
	
	public static function isAjax(){
		return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest";
	}
}
