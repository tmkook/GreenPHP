<?php
//大厅配置
Config::addPath(APPPATH.'/api/common/config/');//加载文件地址
 $config = Config::get("lobby.conf");//获取配置信息
 $config['room_minimum']=join(',',$config['room_minimum']);
 $config['alms']=join(',',$config['alms']);
 $assign['config']=$config;


 if($_POST){
 	$post['room_minimum']=explode(',',$_POST['room_minimum']);
	$post['alms']=explode(',',$_POST['alms']);
	$post['exchange_status']=$_POST['exchange_status'];
	if($_POST['name']){
	foreach ($_POST['name'] as $key => $val){ 		
 	  $goods[$key]['name']=$val;
	  $goods[$key]['type']=$_POST['type'][$key];
	  $goods[$key]['money']=$_POST['money'][$key];
	  $goods[$key]['value']=$_POST['value'][$key];
 	}
	$post['goods']=$goods;
    }
	if($_POST['product']){
	foreach ($_POST['product']['productid'] as $key1 => $val1){ 		
	  $product[$key1]['name']=$_POST['product']['name'][$key1];
	  $product[$key1]['description']=$_POST['product']['description'][$key1];
 	  $product[$key1]['productid']=$val1;
	  $product[$key1]['value']=(float)$_POST['product']['value'][$key1];
	  $product[$key1]['gamemoney']=(int)$_POST['product']['gamemoney'][$key1];
	  $product[$key1]['add']=(float)$_POST['product']['add'][$key1];
 	}
	$post['product']=$product;
    }
 	
 	 Config::set("lobby.conf",$post);
 	$rst=Config::save("lobby.conf");
 	if(is_int($rst)){
 	     HttpQuery::imChatPush('"param":{"cmd":100000}}');
 		exit('{"code":"success","desc":"更新成功！"}');
 	}else {
 		exit('{"code":"error","desc":"更新失败！"}');
 	} 
 }
//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('lobby_config.html',$assign);