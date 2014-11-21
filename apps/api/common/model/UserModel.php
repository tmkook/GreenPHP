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
	protected $table_friends = 'user_friends';
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
		//if($this->getUserInfo(array($type=>$param[$type]))){
		//	throw new Exception("用户已存在", 103);
		//}
		$count = $this->getUins(array($type=>$param[$type]));
		$maxreg = IS_DEV? 50000 : 5;
		if($count['total'] >= $maxreg){
			throw new Exception("您的设备注册已达上限", 103);
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
		$this->addFriend($userinfo['uin'],10000); //添加名车小秘书
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
		HttpQuery::imChatPush('{"param":{"cmd":200100,"uin":'.$userinfo['uin'].'}}');
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
	
	//获取个人资料
	public function getProfile($param){
		if($param['uin'] <= 0){
			throw new Exception("参数错误",101);
		}
		$userinfo = $this->getUserInfo(array('uin'=>$param['uin']));
		$exp = $this->parseExpToLevel($userinfo['exp']);
		if($param['is_friend'] > 0){
			$userinfo['is_friend'] = $this->isFriends($param['uin'],$param['is_friend']);
		}
		$userinfo = array_merge($userinfo,$exp);
		return $userinfo;
	}
	
	/**
	 * 绑定手机-发送验证码
	 *
	 * @param uin  用户id
	 * @param mobile  用户手机号
	 * @return array
	 */
	public function sendBindCode($uin,$mobile){
		if($uin <= 1){
			throw new Exception("uin号码不正确",101);
		}
		if( ! $this->validate->phone($mobile)){
			throw new Exception("手机号码不正确",102);
		}
	
		$row = $this->db->from($this->table_users)->where('uin',$uin)->fetch();
		if($row['mobile'] == $mobile){
			throw new Exception("该帐号已经绑定手机",102);
		}
		$code = mt_rand(10000,99999); //随机生成5位随机数
		$userinfo = array("uin"=>$uin,"mobile"=>$mobile);
		$this->cache->set($code,$userinfo); //存入memcache
		$msg = "校验码".$code."，您正在绑定目睹游戏，需要进行校验【请勿向任何人提供您收到的短信校验码】";
		$rst = MsgSender::send($mobile,$msg);  //发送短信验证
		if($rst != 'true') throw new Exception("验证码发送失败",$rst['code']);
		return $userinfo;
	}
	
	/**
	 * 绑定手机
	 *
	 * @param uin  用户id
	 * @param code  用户验证码
	 * @return array
	 */
	public function bindMobile($uin,$code){
		if($uin <= 1){
			throw new Exception("uin号码不正确",101);
		}
		if(strlen($code) != 5){
			throw new Exception("验证码不正确",102);
		}
		$row = $this->cache->get($code); //读取 memcache
		if(empty($row) || $row['uin'] != $uin){
			throw new Exception("验证码不正确",103);
		}
		$rst = $this->db->update($this->table_users)->set("mobile",$row['mobile'])->where('uin',$uin)->execute(); //绑定手机
		if( ! $rst){
			throw new Exception("操作失败",104);
		}
		$this->cache->del($code); //执行成功后删除该验证码
		return 1;
	}
	
	/**
	 * 找回密码-发送验证码
	 *
	 * @param uin  用户id
	 * @param mobile  用户手机号/邮箱
	 * @param $ismail  是否是邮箱
	 * @return array
	 */
	public function sendFindCode($uin,$mobile,$ismail=0){
		if($ismail){
		 $rst=$this->sendFindEmail($uin,$mobile);
		}else 
         $rst=$this->sendFindMobile($uin,$mobile);

		 return $rst;
	}
	
	/**
	 * 找回密码-发送验证码--短信
	 *
	 * @param uin  用户id
	 * @param mobile  用户手机号
	 * @return array
	 */
	public function sendFindMobile($uin,$mobile){
		if($uin <= 1){
			throw new Exception("uin不正确",101);
		}
		if( ! $this->validate->phone($mobile)){
			throw new Exception("手机号码不正确",102);
		}
		
		$row = $this->db->from($this->table_users)->where('uin',$uin)->where('mobile',$mobile)->fetch();
		if(empty($row['mobile'])){
			throw new Exception("该帐号没有绑定手机",103);
		}
		$code = mt_rand(10000,99999); //随机生成5位随机数
		$userinfo = array("uin"=>$uin,'mobile'=>$mobile);
		$this->cache->set($code,$userinfo); //存入memcache
		$msg = "校验码".$code."，您正在找回目睹游戏帐号，需要进行校验【请勿向任何人提供您收到的短信校验码】";
		$rst = MsgSender::send($mobile,$msg); //发送短信验证
		if($rst != 'true') throw new Exception("验证码发送失败",$rst['code']);

		return $userinfo;

	}
	
	/**
	 * 找回密码-发送验证码--邮件
	 *
	 * @param uin  用户id
	 * @param email  用户邮箱
	 * @return array
	 */
	public function sendFindEmail($uin,$email){
		if($uin <= 1){
			throw new Exception("uin不正确",101);
		}
		if( ! $this->validate->email($email)){
			throw new Exception("邮箱不正确",102);
		}
		
		$row = $this->db->from($this->table_users)->where('uin',$uin)->where('email',$email)->fetch();
		if($row['flag']==2){
			throw new Exception("该邮箱未激活",103);
		}
		if(empty($row['email'])){
			throw new Exception("该帐号没有绑定邮箱",104);
		}
		$code = mt_rand(10000,99999); //随机生成5位随机数
		$userinfo = array("uin"=>$uin,'email'=>$email);
		$this->cache->set($code,$userinfo); //存入memcache
		
		$msg = "<html><body>校验码".$code."，您正在找回目睹游戏帐号，需要进行校验【请勿向任何人提供您收到的邮箱校验码】</body></html>";
		$smtp = Email::connect(array('driver' => 'SmtpMailer'));
        $rst =$smtp->send($email,'找回密码',$msg); 
		return 1;
	}
	
	/**
	 * 找回密码-验证验证码
	 *
	 * @param uin  用户id
	 * @param code  用户验证码
	 * @return array
	 */
	public function verifyFindCode($uin,$code){
		if($uin <= 0){
			throw new Exception("uin号码不正确",101);
		}
		if(strlen($code) != 5){
			throw new Exception("验证码不正确",102);
		}
		$row = $this->cache->get($code); //读取 memcache
		if(empty($row) || $row['uin'] != $uin){
			throw new Exception("验证码不正确",103);
		}
		return 1;
	}
	
	/**
	 * 找回密码-验证验证码
	 *
	 * @param uin  用户id
	 * @param code  用户验证码
	 * @return array
	 */
	public function resetPwd($uin,$code,$password){
		if($uin <= 1){
			throw new Exception("uin号码不正确",101);
		}
		if(strlen($code) != 5){
			throw new Exception("验证码不正确",102);
		}
		$row = $this->cache->get($code); //读取 memcache
		if(empty($row) || $row['uin'] != $uin){
			throw new Exception("验证码不正确",103);
		}
		$pwd_validate =array(array(
				'field'=>'password',
				'label'=>'密码',
				'rules'=> array('required','minlen'=>6,'maxlen'=>32)
		));
		if( ! $this->validate->run(array('password'=>$password),$pwd_validate)){
			throw new Exception($this->validate->getMessage(), 104);
		}
		
		$reset = $this->db->update($this->table_users)->set('password',$this->pwdEncode($password))->where('uin',$uin)->execute();//重置密码
		if( ! $reset){
		   throw new Exception('操作失败',105);	
		}
		$this->cache->del($code); //执行成功后删除该验证码
		return 1;

	}

	/**
	 * 添加好友
	 *
	 * @param string $uin
	 * @param string $friend_uin
	 * @return array 添加结果
	 */
	public function addFriend($uin,$friend_uin){
		if($uin == $friend_uin){
			throw new  Exception("不能添加自己为好友",101);
		}
		if($uin < 10000 || $friend_uin < 10000){
			throw new Exception("uin号码不正确",102);
		}
		$sql = "REPLACE INTO {$this->table_friends} (uin,friend)VALUES({$uin},{$friend_uin}),({$friend_uin},{$uin})";
		$rst = $this->db->getPdo()->query($sql)->execute();
		if( ! $rst){
			throw new Exception("操作失败",103);
		}
		$friendinfo = $this->getProfile(array('uin'=>$friend_uin));
		return $friendinfo;
	}
	
	/**
	 * 删除好友
	 *
	 * @param string $uin
	 * @param string $friend_uin
	 * @return array 添加结果
	 */
	public function delFriend($uin,$friend_uin){
		if($uin == $friend_uin){
			throw new  Exception("操作失败",101);
		}
		if($uin < 10000 || $friend_uin < 10000){
			throw new Exception("uin号码不正确",102);
		}
		$sql = "DELETE FROM {$this->table_friends} WHERE (uin={$uin} AND friend={$friend_uin}) OR (uin={$friend_uin} AND friend={$uin})";
		$rst = $this->db->getPdo()->query($sql)->execute();
		if( ! $rst){
			throw new Exception("操作失败",103);
		}
		return 1;
	}
	
	/**
	 * 获取好友信息
	 *
	 * @param string $uin
	 * @return array 添加结果
	 */
	public function getFriends($uin){
		if($uin < 10000){
			throw new Exception("uin号码不正确",101);
		}
		$sql="SELECT users.uin,users.mobile,users.email,users.nickname,users.gender,users.exp FROM (SELECT friend FROM {$this->table_friends} WHERE uin={$uin}) AS tb1 INNER JOIN users ON tb1.friend=users.uin ORDER BY users.uin ASC";
		$friends = $this->db->getPdo()->query($sql)->fetchAll();
		foreach($friends as $key=>$val){
			$exp = $this->parseExpToLevel($val['exp']);
			$friends[$key]['level'] = $exp['level'];
		}
		return $friends;
	}
	
	//是否是好友
	public function isFriends($uin,$friend){
		if($uin == $friend){
			throw new  Exception("操作失败",101);
		}
		if($uin < 10000 || $friend < 10000){
			throw new Exception("uin号码不正确",102);
		}
		$isfriend = $this->db->from($this->table_friends)->select(null)->select('COUNT(uin)')->where(array('uin'=>$uin,'friend'=>$friend))->fetchColumn();
		return intval($isfriend);
	}
	
	//获取等级信息
	public function getExp($param){
		$exp = $this->db->getPdo()->query("SELECT `exp` FROM {$this->table_users} WHERE uin={$param['uin']} LIMIT 1 FOR UPDATE")->fetchColumn();
		return $this->parseExpToLevel($exp);
	}
	
	//增加经验值
	public function addExp($param){
		$level = $this->getExp($param); //先获取等级信息
		if($level['level'] < 1){
			$param['exp'] = $param['exp'] * 200; // 1-2级加速升级
		}elseif($level['level'] < 2){
			$param['exp'] = $param['exp'] * 40; // 1-2级加速升级
		}
		$param['exp'] += $level['exp'];
		$sql = "UPDATE {$this->table_users} SET exp={$param['exp']} WHERE uin={$param['uin']}";
		$rst = $this->db->getPdo()->query($sql)->execute();
		if( ! $rst){
			throw new Exception("操作失败",101);
		}
		
		$level2 = $this->getExp($param); //获取更新后的等级信息后看是否升级
		//$level2['upgrade'] = 0;
		if($level2['level'] > $level['level']){
			//$level2['upgrade'] = 1;
			//升级
			$reward = array(
						array('min'=>0,'max'=>1,'cash'=>1,'money'=>1000),
						array('min'=>1,'max'=>2,'cash'=>2,'money'=>2000),
						array('min'=>3,'max'=>10,'cash'=>5,'money'=>3000),
						array('min'=>11,'max'=>30,'cash'=>20,'money'=>10000),
						array('min'=>31,'max'=>90,'cash'=>100,'money'=>50000),
						array('min'=>90,'max'=>999999999,'cash'=>300,'money'=>100000),
					);
			foreach($reward as $key=>$val){
				if($level2['level'] >= $val['min'] && $level2['level'] <= $val['max']){
					HttpQuery::api('/common/lobby/sig_reward_add','{"uin":"'.$param['uin'].'","descr":"'.$level2['level'].'级","cash":"'.$val['cash'].'","money":"'.$val['money'].'"}');
					break;
				}
			}
			HttpQuery::imChatPush('{"param":{"cmd":"200300","uin":"'.$param['uin'].'","level":"'.$level2['level'].'"}}');
		}
		return $level2;
	}
	
	//增加苹果设备ID
	public function addDevice($param){
		if(empty($param['uin']) || empty($param['device'])){
			throw new Exception("参数错误",201);
		}
		$sql = "REPLACE INTO apple_devices (uin,device)VALUES('{$param['uin']}','{$param['device']}')";
		$rst = $this->db->getPdo()->query($sql)->execute();
		if( ! $rst){
			throw new Exception("操作失败",101);
		}
		return 1;
	}
	
	//重置消息显示数
	public function resetBadge($param){
		if(empty($param['uin'])){
			throw new Exception("参数错误",201);
		}
		$rst = $this->db->update('apple_devices')->set('badge',0)->where('device',$param['device'])->execute();
		if( ! $rst){
			throw new Exception("操作失败",101);
		}
		return 1;
	}
	
	public function parseExpToLevel($exp){
		if($exp < 1000){
			$level = 0;
			$pre_exp = 0;
			$next_exp = 1000;
		}else{
			$level = 1;
			$next_exp = 1000;
			while($level++){
				$pre_exp = $next_exp;
				$next_exp += 1000 * (1 + $level * 1.2);
				if($next_exp > $exp){
					$level -= 1;
					break;
				}
			}
		}
		return array('exp'=>$exp,'level'=>$level,'next_level'=>$exp-$pre_exp,'next_exp'=>$next_exp-$pre_exp);
	}
	
    //user pwd
    private function pwdEncode($pwd){
        return md5(md5($pwd).md5(crc32($pwd)));
    }


    public function  xyzsOpenid ($param) {
    	$check = json_decode(Http::request('http://passport.xyzs.com/checkLogin.php', array('uid' => $param['username'], 'appid' => '100003983', 'token' => $param['token'])), true);
		
		if($check['ret'] != 0) {
			throw new Exception($param['platform'].'平台验证Token失败:'.$check['error'],101);
		}
    }

    public function openid($param){
    	if(empty($param['platform']) || empty($param['username']) || empty($param['token']) || empty($param['regapp'])) {
    		throw new Exception("参数错误",201);
    	}

    	//验证平台Token是否有效
		$method = $param['platform']."Openid";
		$this->$method($param);

		//查询用户名是否已经存在
		$username = $param['platform'].$param['username'];
		$password = md5('!@Pwd$%'.$username.'@moredoo123');
		$uin = $this->db->from($this->table_users)->select(null)->select('uin')->where('username',$username)->fetchColumn();
		
		//如果用户名不存在，则注册一个uin
		if(empty($uin)) {
			$regparam = array('username'=>$username,'password'=>$password,'regapp'=>$param['regapp'].time(),'platform'=>$param['platform'],'nickname'=>$username,'gender'=>rand(1,2));
			$reg_rst = $this->register($regparam, 'username');
			$uin = $reg_rst['uin'];
		}
		
		return array('uin' => $uin, 'password' => $password);
    }
}