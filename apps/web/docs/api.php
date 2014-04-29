<?php
error_reporting(~E_NOTICE);
$api = dirname(dirname(dirname(__FILE__))).'/api';

define('TESTBASEURL',$_SERVER['HTTP_HOST'].BASEURL);

$apis = array();
foreach((array)glob($api.'/*') as $k=>$v){
	if(is_dir($v)){
		$m = basename($v);
		foreach(glob("{$api}/{$m}/*.php") as $fk=>$fv){
			$c = current(explode('.',basename($fv)));
			if( ! class_exists($c,false)) require_once $fv;
			$reflect = new ReflectionClass($c);
			$methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_FINAL); //获取公共方法
			foreach($methods as $nb=>$mhd){
				$doc = $mhd->getDocComment();
				if($mhd->getName()=='__construct' || empty($doc)){
					unset($methods[$nb]);
				}
			}
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
	if(stripos($parseobj->getName(),'sig_') !== false) $apidetails['extparam'] = '{"sig":"SigString"}';
	$apidetails['type'] = $reflect->getTag('@type');
	//print_r($apidetails);exit;
	$tpl = new Tpl(Config::get('tpl.conf/web'));
	$tpl->display('docs/tpl/api_view.html',array('apis'=>$apidetails));	
}else{
	$tpl = new Tpl(Config::get('tpl.conf/web'));
	$tpl->display('docs/tpl/api_list.html',array('apis'=>$apis));
}
/*
$method = $apis['passport'][0]['methods'][1];
$doc = $method->getDocComment();
$reflect = new ApiDocReflect($doc);
print_r($reflect->getTag('@edit'));
*/
