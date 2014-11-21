<?php
$userinfo = $_SESSION['login_admin'];
if(HttpQuery::isAjax()){
	$vali = array(
		array(
			'field'=>'realname',
			'label'=>'昵称',
			'rules'=> array('required','str','minlen'=>2,'maxlen'=>16)
		),
		array(
			'field'=>'password',
			'label'=>'密码',
			'rules'=> array('minlen'=>6,'maxlen'=>16)
		),
	);
	$validate = new Validate();
	if( ! $validate->run($_POST,$vali)){
		exit('{"code":"error","desc":"'.$validate->getMessage().'"}');
	}
	if(!empty($_POST['password'])){
		$_POST['password'] = md5($_POST['password']);
	}else{
		unset($_POST['password']);
	}
	$db = Database::connect(Config::get('db.conf/default'));
	$rst = $db->update("admin_users")->set($_POST)->where('username',$userinfo['username'])->execute();
	if($rst){
		$_SESSION['login_admin'] = array_merge($_SESSION['login_admin'],$_POST);
		exit('{"code":"success","desc":"修改成功！"}');
	}else{
		exit('{"code":"error","desc":"修改失败！"}');
	}
}

//加载视图
$assign = array('userinfo'=>$userinfo);
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('edit.html',$assign);