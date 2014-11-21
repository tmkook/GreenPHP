<?php
/**
* 用户资料类
*
* 用于操作用户信息及用户登录认证
*
* @package api
* @access public
* @version $Id$
* @author $Author$
*/
class user
{
	protected $user;
	protected $query;

	public function __construct() {
		//加载用户模型
		require_once dirname(__FILE__) . '/model/UserModel.php';
		$this->user = new UserModel();
	}
	
    /**
    *
    * 注册新用户
	* 使用设备号注册时{"regapp":"...","password":"...","platform":"coolcar"}
	* 使用用户名注册时{"username":"...","password":"...","platform":"web"}
	* 不是必填字段可以不传
	*
	* @param username stirng N 用户名
	* @param regapp string required 注册设备号
	* @param password string required 密码
	* @param platform string required 注册平台(如qq、sina)
	* @param nickanme string required 昵称
	* @param mobile string N 手机号
	* @param email string N 邮箱
	* @param realname string N 真实改名
	* @param gender integer N 1为男2为女默认为0未知
	* @param logapp string N 登录设备(获取设备号)
	*
	* @request JSON {"regapp":"e10adc3949ba59abbe56e057f20f883e","password":"e10adc3949ba59abbe56e057f20f883e","platform":"test"}
	* @success JSON {"code":100,"data":{"uin":"4","username":"leasdfsf","email":"7040494@qq.com","realname":"isme","nickname":"昵称","regtime":1397790198}}
	* @error JSON {"code":101,"desc":"用户名已被注册"}
	*
	* @data uin 用户ID
	* @data username 用户名
	* @data mobile 手机号
	* @data email 邮箱
	* @data nickanme 昵称
	* @data realname 真实改名
	* @data gender 性别
	* @data regtime 注册时间
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 2014-04-08
    */
	public function register() {
		$param = HttpQuery::param();
		$type = empty($param['username'])? 'regapp' : 'username';
		return $this->user->register($param,$type);
	}
	
	/**
    *
    * 获取用户资料
	*
	* @param uin integer required 需要获取资料的用户ID
	* @param is_friend integer N 检查是否是好友(不需检查可不传)
	*
	* @request JSON {"uin":"2","is_friend":"200000"}
	* @success JSON {"code":100,"desc":"success","data":{"uin":"2","username":"","nickname":"我的新昵称","realname":null,"gender":"0","exp":"2200","is_friend":0,"level":2,"next_exp":3400,"next_level":3}}
	* @error JSON {"code":201,"desc":"用户不存在"}
	*
	* @data is_friend 是否是好友(0不是,否则是好友)
	* @data level 玩家当前等级
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 2014-04-08
    */
	public function get_userinfo(){
		$param = HttpQuery::param();
		$userinfo = $this->user->getprofile($param);
		unset($userinfo['password'],$userinfo['username'],$userinfo['realname'],$userinfo['platform'],$userinfo['logplatform'],$userinfo['regapp'],$userinfo['logapp'],
		$userinfo['regtime'],$userinfo['flag'],$userinfo['money'],$userinfo['msgid'],$userinfo['money_verify']);
		return $userinfo;
	}
	
    /**
    *
    * 用户登录签名
	* 验证用户id与密码通过后生成签名，安全接口需要验证正确的签名后才能调用成功
	* 签名有效期为 1800 秒，每请求一次接口自动续时
	*
	* @param uin stirng N 用户ID
	* @param password md5(string) required 用户密码
	* @param logapp string required 登录设备号
	* @param platform string required 登录渠道平台
	*
	* @request JSON {"uin":"2","password":"e10adc3949ba59abbe56e057f20f883e","logapp":"123456","platform":"moredoo"}
	* @success JSON {"code":100,"desc":"success","data":{"userinfo":{"uin":"10000","username":"admin","realname":"真实姓名","nickname":"昵称","sig":"sigString"}}
	* @error JSON {"code":101,"desc":"用户名或密码错误"}
	*
	* @data userinfo 用户资料(字段解释请参考login接口)
	* @data sig 用户登录签名(sig_ 开头的接口都需要此参数)
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 增加返回字段
    */
	public function sig(){
		$param = HttpQuery::param();
		if($param['uin'] == 1){
			throw new Exception("参数错误",101);
		}
		$data = $this->user->id_login($param);
		$usersig = new UserSig();
		$data['sig'] = $usersig->sig($data);
		return array('userinfo'=>$data);
	}
	
    /**
    *
    * 验证用户签名
	* 验证用户登录签名是否正确
	*
	* @param uin stirng required 用户ID
	* @param sig md5(string) required 签名
	*
	* @request JSON {"uin":"2","sig":"sigString"}
	* @success JSON {"code":100,"desc":"success","data":{"userinfo":{"uin":"10000","username":"admin","realname":"真实姓名","nickname":"昵称"}}
	* @error JSON {"code":201,"desc":"签名不正确"}
	*
	* @data userinfo 用户资料(字段解释请参考login接口)
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 2014-04-08
    */
	public function verify_sig(){
		$param = HttpQuery::param();
		$usersig = new UserSig();
		$userinfo = $usersig->verifySig($param['sig']);
		if($userinfo['uin'] != $param['uin']){
			throw new Exception("签名不正确",201);
		}
		return array('userinfo'=>$userinfo);
	}

    /**
    * 重置用户密码
	* 修改密码
	*
	* @param uin integer required 修改的用户ID
	* @param password string required 当前密码
	* @param newpassword string required 新密码
	*
	* @request JSON {uin:"2","password":"e10adc3949ba59abbe56e057f20f883e","newpassword":"md5(12345678)"}
	* @success JSON {"code":100,"data":[]}
	* @error JSON {"code":101,"desc":"修改失败"}
	*
	* @data uin 用户ID
	* @data username 用户名
	* @data mobile 手机号
	* @data email 邮箱
	* @data nickanme 昵称
	* @data realname 真实改名
	* @data gender 性别
	* @data regtime 注册时间
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 2014-04-08
    */
	public function sig_setpwd(){
		$param = HttpQuery::param();
		$userinfo = $this->user->setPwd($param['uin'],$param['password'],$param['newpassword']);
		return array('userinfo'=>$userinfo);
	}
}
