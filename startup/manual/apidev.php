<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>GreenPHP用户手册</title>
<style>
body{font-size:12px;}
h3{font-size:20px;margin-top:30px;}
h4{font-size:14px;}
ul{line-height:25px;}
b{background:#DDD;font-weight:normal;padding:0 4px;}
code{display:block;background:#FCF0C1;padding:0 10px;border:solid 1px #FADC96; line-height:20px;}
</style>
</head>
<body>
<h3>1、定义API</h3>
<p>如果你想开发一个API，首先得定义好API名称和传入输出的参数，然后再进行代码编写。</p>
<p>下面我们编写一个获取用户资料的接口，然后设定他的输入输出参数：</p>
<p>接口名：get_userinfo</p>
<p>传入参数：(int)uin</p>
<p>输出参数：数据库中该用户的字段</p>
<p>创建 apps/api/common/user.php 接口控制器文件并输入以下代码，代码中注释部份将用于生成API文档及测试用例。</p>
<code>
<pre>
&lt;?php
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
	* @param uin integer required 需要获取资料的用户ID //输入参数
	*
	* @request JSON {"uin":"2"} //测试用例使用的参数
	* @success JSON {"code":100,"desc":"success","data":{"uin":"2","nickname":"我的新昵称","gender":"0"}} //成功返回的内容
	* @error JSON {"code":201,"desc":"用户不存在"} //错误返回的内容
	*
	* //输出的字段说明
	* @data username 用户名
	* @data nickname 用户昵称
	*
	* @type GET,POST //支持请求类型
	* @author Leon 7040494@qq.com //作者信息
	* @edit Leon 2014-04-08 //编辑者信息
	*/
	public function get_userinfo(){
		$param = HttpQuery::param(); //获取请求的参数
		$userinfo = $this->user->getUserInfo($param['uin']); //获取用户信息
		return $userinfo; //返回信息
	}
}
</pre>
</code>
<p>此时控制器已经完成，下面创建一个 apps/api/common/model/UserModel.php 文件，用于完成具体的数据获取操作。</p>
<code>
<pre>
&lt;?php
class UserModel
{
	protected $table_users = 'users'; //数据库用户表
	protected $db; //数据库对象
	public function __construct() {
		$this->db = Database::connect(Config::get('db.conf/default')); //连接数据库
	}
	
	public function getUserInfo($uin) {
		$userinfo = $this->db->from($this->table_users)->where("uin",$uin)->orderBy('regtime DESC')->limit(1)->fetch();
		if(empty($userinfo)){
			throw new Exception("用户不存在",101); //如果用户不存在抛出一个错误信息
		}
		return $userinfo; //返回用户信息
	}
}
</pre>
</code>
<p>到这里get_userinfo接口就完成了，你可以打开 <a href="http://localhost<?php echo BASEURL ?>/startup/docs/api" target="_blank">http://localhost<?php echo BASEURL ?>/startup/docs/api</a>文档页面查看该接口并进行测试了。</p>
<p>具体项目代码请查看 apps/api/common/ 目录下的代码。</p>
</body>
</html>
