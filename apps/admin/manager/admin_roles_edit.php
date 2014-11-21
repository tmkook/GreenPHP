<?php
$assign = array(
	'flag' => array(1=>'正常',0=>'冻结'),
	'edit' => array()
);
if(HttpQuery::isAjax()){
	if(empty($_POST['rolename'])){
		exit('{"code":"error","desc":"角色名称不能为空"}');
	}
	$db = Database::connect(Config::get('db.conf/default'));
	$id = ($_POST['id'] > 0)? (int)$_POST['id'] : '';
	$access = json_encode((array)$_POST['access']);
	
	$sql = "REPLACE INTO admin_users_roles(id,rolename,access,flag)VALUES('{$id}','{$_POST['rolename']}','{$access}','{$_POST['flag']}')";
	$rst = $db->getPdo()->query($sql);
	if($rst){
		exit('{"code":"success","desc":"编辑成功！"}');
	}else{
		exit('{"code":"error","desc":"编辑失败！"}');
	}
}

if(isset($_GET['roleid']) && $_GET['roleid'] > 0){
	$db = Database::connect(Config::get('db.conf/default'));
	$edit = $db->from('admin_users_roles')->where('id',$_GET['roleid'])->fetch();
	if(empty($edit)){
		exit('角色不存在');
	}
	$edit['access'] = json_decode($edit['access'],true);
	$assign['edit']  = $edit;
}

$roles = new AdminRoles();
$assign['menus'] = $roles->getAllMenus();

$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('admin_roles_edit.html',$assign);