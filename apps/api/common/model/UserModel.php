<?php
class UserModel
{
	//protected $table_users = 'users'; //数据库用户表
	//protected $db; //数据库对象
	public function __construct() {
		//$this->db = Database::connect(Config::get('db.conf/default')); //连接数据库
	}
	
	public function getUserInfo($uin) {
		//$userinfo = $this->db->from($this->table_users)->where("uin",$uin)->orderBy('regtime DESC')->limit(1)->fetch();
		//if(empty($userinfo)){
		//	throw new Exception("用户不存在",101); //如果用户不存在抛出一个错误信息
		//}
		if($uin != 123456){
			throw new Exception("用户不存在",101); //如果用户不存在抛出一个错误信息
		}
		$userinfo = array('username'=>'tmkook','uin'=>'123456','gender'=>0);
		return $userinfo; //返回用户信息
	}
}