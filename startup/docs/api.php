<?php
error_reporting(~E_NOTICE);

//运营环境验证authkey才能访问
session_start();
if(IS_DEV == 0 && $_SESSION['doc_view'] != 1){
	if($_GET['authkey'] == 'moredoo'){
		$_SESSION['doc_view'] = 1;
	}else{
		exit('forbid');
	}
}

//遍历api
$api = dirname(dirname(dirname(__FILE__))).'/apps/api';
define('TESTBASEURL',$_SERVER['HTTP_HOST'].BASEURL);
$apis = array();

foreach((array)glob($api.'/*') as $k=>$v){
	if(is_dir($v)){
		$m = basename($v);
		if(in_array($m,array('exchange','global','passport','reward','mobile'))) continue;
		foreach(glob("{$api}/{$m}/*.php") as $fk=>$fv){
			$c = current(explode('.',basename($fv)));
			if( ! class_exists($c,false)) require_once $fv;
			$reflect = new ReflectionClass($c);
			if( ! $reflect->getDocComment()) continue;
			$methods_rst = $reflect->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_FINAL); //获取公共方法
			$methods = array();
			foreach($methods_rst as $nb=>$mhd){
				$doc = $mhd->getDocComment();
				if($mhd->getName() !== '__construct' && !empty($doc)){
					$methods[$mhd->getName()] = $mhd;
				}
			}
			//ksort($methods);
			$methods = array_values($methods);
			$apis[$m][] = array(
				'name'=>$c,
				'methods'=>$methods,
				'rowspan'=>count($methods),
			);
		}
	}
}

if(isset($_GET['vm']) && isset($_GET['vc']) && isset($_GET['vt'])){
	$parseobj = null;
	foreach($apis as $m=>$cs){
		if($m==$_GET['vm']){
			foreach($cs as $ts){
				if($ts['name']==$_GET['vc']){
					foreach($ts['methods'] as $tobj){
						if($tobj->getName() == $_GET['vt']){
							$parseobj = $tobj;
							break;
						}
					}
					break;
				}
			}
			break;
		}
	}
	if(!is_object($parseobj)) exit('api not found');
	$reflect = new ApiDocReflect($parseobj->getDocComment());
	$apidetails = array();
	$apidetails['descr'] = $reflect->getDescr();
	$apidetails['params'] = $reflect->getParams();
	$apidetails['example'] = $reflect->getExample();
	$apidetails['data'] = $reflect->getData();
	$apidetails['author'] = $reflect->getTag('@author');
	$apidetails['edit'] = $reflect->getTag('@edit');
	if(stripos($parseobj->getName(),'sig_') !== false){
		$apidetails['extparam'] = '{"sig":"SigString","reqapp":"test_php"}';	
	}else{
		$apidetails['extparam'] = '{"reqapp":"test_php"}';
	}
	$apidetails['type'] = implode(',',$reflect->getTag('@type'));
	$tplfile = 'api_view.html';
	$assign = array('apis'=>$apidetails);
}else{
	$tplfile = 'api_list.html';
	$assign = array('apis'=>$apis);
}

$tpl = new Tpl(Config::get('tpl.conf/startup'));
$tpl->display($tplfile,$assign);
