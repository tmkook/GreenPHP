<?php
class HttpQuery
{
	protected static $param;
	protected static $extparam;
	
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
	
	public static function imChatPush($param,$is_async=true){
		if($is_async){
			$rst = HttpQuery::apiAsync('/service/notification/imchat_push',$param,array('reqapp'=>'imChatPush'));
		}else{
			$rst = HttpQuery::api('/service/notification/imchat_push',$param,array('reqapp'=>'imChatPush'));
		}
		return $rst;
	}
	
	public static function imCapture($param,$is_async=true){
		if($is_async){
			$rst = HttpQuery::apiAsync('/service/notification/im_capture',$param,array('reqapp'=>'imCapture'));
		}else{
			$rst = HttpQuery::api('/service/notification/im_capture',$param,array('reqapp'=>'imCapture'));
		}
		return $rst;
	}

	public static function imConfig($param,$is_async=true){
		if($is_async){
			$rst = HttpQuery::apiAsync('/service/notification/im_config',$param,array('reqapp'=>'imConfig'));
		}else{
			$rst = HttpQuery::api('/service/notification/im_config',$param,array('reqapp'=>'imConfig'));
		}
		return $rst;
	}

	public static function imIp($param,$is_async=true){
		if($is_async){
			$rst = HttpQuery::apiAsync('/service/notification/im_ip',$param,array('reqapp'=>'imIp'));
		}else{
			$rst = HttpQuery::api('/service/notification/im_ip',$param,array('reqapp'=>'imIp'));
		}
		return $rst;
	}

	public static function imVersion($param,$is_async=true){
		if($is_async){
			$rst = HttpQuery::apiAsync('/service/notification/im_version',$param,array('reqapp'=>'imVersion'));
		}else{
			$rst = HttpQuery::api('/service/notification/im_version',$param,array('reqapp'=>'imVersion'));
		}
		return $rst;
	}
	
	public static function applePush($param){
		HttpQuery::apiAsync('/service/notification/apple_push',$param,array('reqapp'=>'applePush'));
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
