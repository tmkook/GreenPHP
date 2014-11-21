<?php
$db = Database::connect(Config::get('db.conf/default'));
$assign = array();

$id=empty($_REQUEST['id'])?'':$_REQUEST['id'];
if($id){
  $user = $db->from('admin_users')->select(null)->select('*')->where('id',$id)->fetch();
  $assign['user']=$user;
}
if(HttpQuery::isAjax()){
	$vali = array(
		array(
				'field'=>'username',
				'label'=>'用户名',
				'rules'=> array('required','str','minlen'=>4,'maxlen'=>16)
		),
		array(
				'field'=>'password',
				'label'=>'密码',
				'rules'=> array('required','minlen'=>6,'maxlen'=>32)
		),
		array(
				'field'=>'realname',
				'label'=>'真实姓名',
				'rules'=> array('required','str','minlen'=>4,'maxlen'=>16)
		),
);
if($id){  //修改账号
    $password=empty($_POST['password'])?$user['password']:md5($_POST['password']);
	$sql = "REPLACE INTO admin_users(id,username,realname,password,platform,role,createtime)VALUES('{$id}','{$_POST['username']}','{$_POST['realname']}','{$password}','{$_POST['platform']}','{$_POST['role']}','{$user['createtime']}')";
	$rst = $db->getPdo()->query($sql);
	
}else{ 
  //添加账号
$validate = new Validate();
if( ! $validate->run($_POST,$vali)){
	exit('{"code":"error","desc":"'.$validate->getMessage().'"}');
}
if($db->from("admin_users")->where(array('username'=>$_POST['username']))->fetch('id')){
    exit('{"code":"error","desc":"用户已存在"}');
}
$values=array('username'=>$_POST['username'],'realname'=>$_POST['realname'],'password'=>md5($_POST['password']),'platform'=>$_POST['platform'],'role'=>(int)$_POST['role'],'createtime'=>time());
$rst=$db->insertInto("admin_users",$values)->execute();
}
	if($rst){
		exit('{"code":"success","desc":"执行成功！"}');
	}else{
		exit('{"code":"error","desc":"执行失败！"}');
	}

}

$assign['roles'] = $db->from('admin_users_roles')->select(null)->select('id,rolename')->where('flag',1)->fetchAll();

//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('admin_user_add.html',$assign);