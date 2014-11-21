<?php
//声明视图变量
$assign = array();

//验证表单
$validate = new Validate();
if(!empty($_POST['username']) && !empty($_POST['password'])){
	try{
		$userSig = new UserSig();
		$userSig->loginErrorNumCheck($_POST['username']);
		$db = Database::connect(Config::get('db.conf/default'));
		$query = $db->from('admin_users')->where('username',$_POST['username'])->limit(1);
		$userinfo = $query->fetch();
		if(empty($userinfo) || $userinfo['password'] != md5($_POST['password'])){
			$userSig->loginErrorNumAdd($_POST['username']);
			throw new Exception('用户名或密码不正确');
		}else{
			$roles = new AdminRoles();
			$role = $roles->getUserRole($userinfo['id']);
			$userinfo['rolename'] = $role['rolename'];
			$userinfo['access'] = $role['access'];
			$_SESSION['login_admin'] = $userinfo;
			//if($_POST['mark'] == 1){
				//setcookie('PHPSESSID',session_id(),time()+86400 * 7,'/');
			//}
			header('location:'.BASEURL.'/admin/mgr/index');
		}
	}catch(Exception $e){
		$assign['form_msg'] = $e->getMessage();
	}
}

function checkErrorNum(){
	$max = 5;
	$info = $_SESSION[$_SERVER['REMOTE_ADDR']];
	if(date('Ymd') != date('Ymd',$info['time'])){
		$_SESSION[$_SERVER['REMOTE_ADDR']] = array();
	}
	if($info['num'] >= $max){
		$wait = (ceil($info['num'] / $max) - 1) * 600;
		if((time() - $info['time']) < $wait){
			$m = $wait / 60;
			setError();
			throw new Exception("您已多次密码错误，请{$m}分钟后再试");
		}
	}
}

function setError(){
	$info = $_SESSION[$_SERVER['REMOTE_ADDR']];
	$info['num'] += 1;
	$info['time'] = time();
	$_SESSION[$_SERVER['REMOTE_ADDR']] = $info;
}

//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('login.html',$assign);
