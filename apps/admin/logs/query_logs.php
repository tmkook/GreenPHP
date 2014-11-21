<?php
//声明视图变量
$assign = array();

//获取所有日志表
$logs = Config::get('logs.conf');
$assign['tables'] = $logs;


//接收并准备参数
$where=array();
$from = $_GET['table'];
$startime=empty($_GET['start'])?'':$_GET['start'];
$endtime=empty($_GET['end'])?'':$_GET['end'];
if(empty($_GET['msg'])) {
	empty($_GET['start'])? '' : $where = array_merge($where,array('created>?'=>strtotime($_GET['start'])));
	empty($_GET['end'])? '' : $where = array_merge($where,array('created<?' =>strtotime($_GET['end'])));
} else {
	$where = " msg like '%" . $_GET['msg'] . "%' ";
	empty($_GET['start'])? '' : $where .= " and created > ". strtotime($_GET['start']);
	empty($_GET['end'])? '' : $where .= " and created < " . strtotime($_GET['end']);
}


if(!empty($_GET['table'])) {
	$db = Database::connect(Config::get('db.conf/default'));
	if(empty($_GET['msg'])) {
		$total  = $db->from($from)->select(null)->select('COUNT(*) AS total')->where($where)->fetch('total');
	} else {
		$total  = $db->from($from)->select(null)->select('COUNT(*) AS total')->where(null)->where($where)->fetch('total');
	}
	$page = new Paging($total,intval($_GET['page']));
	$limit = $page->getLimit();
	
	$assign['page_total_rows'] = $total;
	$assign['show_page'] = $page->getShow();
	$assign['last_page'] = $page->getLast();
	if(empty($_GET['msg'])) {
		$assign['lists'] = $db->from($from)->orderBy('id DESC')->limit(implode(',',$limit))->where($where)->fetchAll();
	} else {	
		$assign['lists'] = $db->from($from)->where(null)->where($where)->orderBy('id DESC')->limit(implode(',',$limit))->where($where)->fetchAll();
	}
}

$assign['start']=$startime;
$assign['end']=$endtime;
//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('query_logs.html',$assign);
