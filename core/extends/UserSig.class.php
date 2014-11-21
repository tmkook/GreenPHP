<?php
class UserSig
{
	protected $cache;
	protected $signame = 'UserSig';
	protected $encode_key = '@More*DoloSi$g1Key$%A%D2';
	public function __construct(){
		$this->cache = Cache::connect(Config::get('cache.conf/sso_mem'));
	}
	
	public function resetSig($userinfo,$sig){
		$this->cache->set($sig,$userinfo);
		return $userinfo;
	}

	public function sig($userinfo){
		$sig = md5(json_encode($userinfo));
		$clientip = isset($_SERVER['HTTP_CLIENT_IP'])? $_SERVER['HTTP_CLIENT_IP'] : '';
		$remote = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '';
		$forwarded = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
		$sig = md5($sig.$clientip.$remote.$forwarded.time()).$userinfo['uin'];
		$this->cache->set($sig,$userinfo);
		return $sig;
	}
	
	//获取COOKIE中解密后的Sig签名
	public function getSigStr(){
		$sig = $_COOKIE[$this->signame];
		$authcode = new Encrypt($this->encode_key,$expire);
		return $authcode->decode($sig); //解密后
	}
	
	//获取登录的用户信息 gologin=1 没登录跳转到登录页
	public function getRemember($gologin=0){
		$sig = $this->getSigStr();
		if(empty($sig)){
			$siginfo = null;
		}else{
			$siginfo = $this->cache->get($sig);
		}
		if(empty($siginfo['uin'])){
			if($gologin) header("Location:".BASEURL."/view/home/login");
		}else{
			$this->cache->set($sig,$siginfo);//续时
		}
		return $siginfo;
	}
	
	//登录接口验证成功后将Sig保存到Cookie
	public function setRemember($sig,$mark=0){
		$expire = 0;
		if($mark){
			$expire = 86400*30;
		}
		$authcode = new Encrypt($this->encode_key,$expire);
		$sig = $authcode->encode($sig); //加密后
		setcookie($this->signame,$sig,$expire + time(),'/');
	}

	public function verifySig($sig){
		if(empty($sig)){
			return null;
		}
		$siginfo = $this->cache->get($sig);
		if(empty($siginfo)){
			throw new Exception("无效的签名",101);
		}
		$this->cache->set($sig,$siginfo);//续时
		return $siginfo;
	}
	
	public function unsetSig($sig){
		$this->cache->del($sig);
	}
	
	public function loginErrorNumCheck($sig,$max=5){
		$info = $this->cache->get($sig);
		if(!empty($info) && date('Ymd') != date('Ymd',$info['time'])){
			$this->cache->del($sig);
			$info = array();
		}
		
		if($info['num'] >= $max){
			if(($info['num'] % $max) == 0){
				$errnum = (ceil($info['num'] / $max));
				if($errnum > $max){
					throw new Exception("您已多次密码错误，今天已禁止登录");
				}
				$wait = $errnum * 600;
				if((time() - $info['time']) < $wait){
					$m = $wait / 60;
					throw new Exception("您已第{$info['num']}次密码错误，请{$m}分钟后再试");
				}
			}
		}
	}

	public function loginErrorNumAdd($sig){
		$info = $this->cache->get($sig);
		$info['num'] += 1;
		$info['time'] = time();
		$this->cache->set($sig,$info);
	}


}
