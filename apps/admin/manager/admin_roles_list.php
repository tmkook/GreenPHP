<?php
//声明视图变量
$assign = array(
	'fields' => array(
		"rolename"=>"角色名",
	),
	'flag' => array('冻结','正常'),
);

$where=array();
if(!empty($_GET['field']) && !empty($_GET['wd'])){
	$where[$_GET['field']] = $_GET['wd'];
}

$db = Database::connect(Config::get('db.conf/default'));
$total  = $db->from('admin_users_roles')->select(null)->select('COUNT(*) AS total')->where($where)->fetch('total');
$page = new Paging($total,intval($_GET['page']));
$limit = $page->getLimit();

$assign['page_total_rows'] = $total;
$assign['show_page'] = $page->getShow();
$assign['last_page'] = $page->getLast();
$assign['lists'] = $db->from('admin_users_roles')->orderBy('id DESC')->limit(implode(',',$limit))->where($where)->fetchAll();

//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('admin_roles_list.html',$assign);