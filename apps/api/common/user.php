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
	public function __construct() {
		require_once dirname(__FILE__).'/model/UserModel.php'; //加载用户模型
		$this->user = new UserModel();
	}
	
	/**
	* 获取用户资料
	*
	* @param uin integer required 需要获取资料的用户ID
	*
	* @request JSON {"uin":"123456"}
	* @success JSON {"code":100,"desc":"success","data":{"uin":"123456","username":"tmkook","gender":"0"}}
	* @error JSON {"code":201,"desc":"用户不存在"}
	*
	* @data username 用户名
	* @data uin 用户id
	*
	* @type GET,POST
	* @author Leon 7040494@qq.com
	* @edit Leon 2014-12-08
	*/
	public function get_userinfo(){
		$param = HttpQuery::param(); //获取请求的参数
		$userinfo = $this->user->getUserInfo($param['uin']); //获取用户信息
		return $userinfo; //返回信息
	}
}