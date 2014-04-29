<?php
class UserSig
{
	protected $cache;
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
		$sig = md5($sig.$clientip.$remote.$forwarded.time()).$userinfo['id'];
		$this->cache->set($sig,$userinfo);
		return $sig;
	}

	public function verifySig($sig){
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

}
