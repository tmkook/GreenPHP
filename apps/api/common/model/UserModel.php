<?php
/**
* 这是一个用户模块
*
* 这个模块封装user类,实现注册、登陆、重置密码、获取用户信息
*
* @package api
* @subpackage model
* @access protected
* @version $Id$
* @author $Author$
*/
class UserModel
{
	protected $table_users = 'users';
	protected $db;
	protected $validate;
	public $cache;
	
	public function __construct() {
		$this->cache = Cache::connect(Config::get('cache.conf/mobi_mem'));
		$this->db = Database::connect(Config::get('db.conf/default'),true);
		$this->validate = new Validate();
	}
	
	//通用注册
	public function register($param,$type){
		//验证注册信息
		if( ! $this->validate->run($param,Config::get("validate.conf/{$type}_register"))){
            throw new Exception($this->validate->getMessage(), 101);
        }
		//公共参数验证
		if( ! $this->validate->run($param,Config::get("validate.conf/register"))){
            throw new Exception($this->validate->getMessage(), 101);
        }
		//注册用户
		if(empty($param['platform'])) $param['platform'] = 'moredoo';
		if(empty($param['gender'])) $param['gender'] = '1';
        $param['password'] = $this->pwdEncode($param['password']);
        $param['regtime'] = time();
		if(empty($param['nickname'])) $param['nickname'] = ''; //默认昵称
        $uin = $this->db->insertInto($this->table_users, $param)->execute();
		$userinfo = $this->getUserInfo(array('uin'=>$uin));
		unset($userinfo['password'],$userinfo['flag']);
        return $userinfo;
	}
	
	//通用登录
	public function id_login($param){
		//验证登录信息
		if( ! $this->validate->run($param,Config::get("validate.conf/id_login"))){
            throw new Exception($this->validate->getMessage(), 101);
        }
		//验证公共登录信息
		if( ! $this->validate->run($param,Config::get("validate.conf/login"))){
            throw new Exception($this->validate->getMessage(), 102);
        }
		$login = $this->getUserInfo(array('uin'=>$param['uin']));
        if($login['password'] != $this->pwdEncode($param['password'])){
            throw new Exception('用户名或密码不正确',103);
        }
        if($login['flag'] == 0){
            throw new Exception('帐号已被禁止登录',104);
        }
		if(empty($param['platform'])) $param['platform'] = 'moredoo';
		$this->db->update($this->table_users)->set(array('logplatform'=>$param['platform'],'logapp'=>$param['logapp'],'sigtime'=>time()))->where('uin',$login['uin'])->execute();
		$level = $this->parseExpToLevel($login['exp']);
		$login['level'] = $level['level'];
        unset($login['password'],$login['platform'],$login['regapp'],$login['flag'],$login['money_verify'],$login['money'],$login['exp'],$login['sigtime']);//安全字段不返回
		return $login;
	}
	
	/**
	 * 重置密码，用于判断用户旧密码是否正确，检查新信息是否合法，更新用户密码
	 * 
	 * 获得用户名，旧密码，新密码
	 * @param string $uin
	 * @param string $password
	 * @param string $newpassword
	 * @return array 用户信息
	 */
	public function setPwd($uin, $password, $newpassword){
        //验证用户
        if($password == $newpassword){
            throw new Exception('新密码不能与当前密码相同',101);
        }
		$pwd_validate = array(
			array(
				'field'=>'password',
				'label'=>'密码',
				'rules'=> array('required','minlen'=>6,'maxlen'=>32)
			),
			array(
				'field'=>'curpassword',
				'label'=>'原密码',
				'rules'=> array('required','minlen'=>6,'maxlen'=>32)
			),
		);
		if( ! $this->validate->run(array('curpassword'=>$password,'password'=>$newpassword),$pwd_validate)){
            throw new Exception($this->validate->getMessage(), 102);
        }
		$userinfo = $this->getUserInfo(array('uin'=>$uin));
		if($userinfo['password'] != $this->pwdEncode($password)){
            throw new Exception('当前密码不正确',103);
        }
        //重置密码
		$reset = $this->db->update($this->table_users)->set('password',$this->pwdEncode($newpassword))->where('uin',$uin)->execute();
        if(!$reset){
            throw new Exception('操作失败',104);
        }
		unset($userinfo['password'],$userinfo['flag']);//安全字段不返回
        return $userinfo;
	}
	
	//重置资料
	public function edit($userinfo){
		unset($userinfo['password'],$userinfo['platform'],$userinfo['regapp'],$userinfo['flag'],$userinfo['money_verify'],
		$userinfo['money'],$userinfo['exp'],$userinfo['mobile'],$userinfo['email']);//禁止修改的字段
		if(isset($userinfo['nickname']) && trim($userinfo['nickname']) == ''){
			throw new Exception("昵称不能为空",101);
		}elseif(isset($userinfo['realname']) && trim($userinfo['realname']) == ''){
			throw new Exception("真实姓名不能为空",102);
		}elseif(isset($userinfo['gender']) && $userinfo['gender'] > 2){
			throw new Exception("性别输入不正确",103);
		}
		$reset = $this->db->update($this->table_users)->set($userinfo)->where('uin',$userinfo['uin'])->execute();
        if(!$reset){
            throw new Exception('操作失败',104);
        }
        return $userinfo;
	}
	
	//获取设备注册的帐号信息
	public function getUins($where){
		$users = $this->db->from($this->table_users)->where($where)->orderBy('regtime DESC')->fetchAll();
		return array('total'=>count($users),'last'=>array('uin'=>$users[0]['uin'],'nickname'=>$users[0]['nickname']));
	}
	
	/**
	 * 获取用户的信息
	 * 
	 * 获得用户名，获得用户信息
	 * @param string $username 用户名
	 * @return array 用户信息
	 */
	public function getUserInfo($where) {
		$userinfo = $this->db->from($this->table_users)->where($where)->orderBy('regtime DESC')->limit(1)->fetch();
		if(empty($userinfo)){
			throw new Exception("用户不存在",101);
		}
		return $userinfo;
	}
	
    //user pwd
    private function pwdEncode($pwd){
        return md5(md5($pwd).md5(crc32($pwd)));
    }

}