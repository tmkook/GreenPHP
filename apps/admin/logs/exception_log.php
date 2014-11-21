<?php
//加载业务名称
$list=array();

if($_GET['log_host']){
	$log_host=empty($_GET['log_host'])?'':$_GET['log_host'];
	$log_name=empty($_GET['log_name'])?'':$_GET['log_name'];
	
	$param=array(
			"cmd"=>"tail-exit-log",
			"host"=>$log_host,
			"name"=>$log_name,
			"lib"=>"serverControl"
			);
	$lists 	= HttpQuery::imCapture(json_encode($param),false);
	$arr	= $lists['data']['master-server-1']['data'];
	if($arr) {
		foreach ($arr as $key=>$vals){
		if($vals){
			$val=json_decode($vals,true);
			$list[$key]['date'] =$val['date']." ".$val['time'];
			$list[$key]['pid']   =$val['pid'];
			$list[$key]['uptime'] =$val['uptime'];
			$list[$key]['desc'] =$val['desc'];
			$list[$key]['stack'] =$val['stack'];
			$list[$key]['args'] =$val['args'];
			$list[$key]['args'][7]=urldecode($val['args'][7]);

		}	
		}
	krsort($list);
	//print_r($list);
	}
}

$assign['log_host']=$log_host;
$assign['log_name']=$log_name;
$assign['lists']=$list;
//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('exception_log.html',$assign);
