<?php
//接口地址
$url = urldecode(base64_decode($_GET['api']));
$parse = parse_url($url);
$query = $parse['query'];

$assign = array();
$assign['type'] = empty($_GET['type'])? 'POST' : current((array)explode(',',$_GET['type']));
$assign['api'] = current(explode('?',$url));
$assign['param'] = $query;

if(!isset($_COOKIE['test_sig'])){
	$parameter = array('parameter'=>'{"uin":2,"password":"e10adc3949ba59abbe56e057f20f883e","logapp":"test_php"}','extparam'=>'{"reqapp":"test_php"}');
	$rst = Http::request("http://{$_SERVER['HTTP_HOST']}".BASEURL."/api/passport/user/sig",$parameter,'');
	if($rst){
		$userinfo = json_decode($rst,true);
		if($userinfo['code'] == 100){
			$userinfo = $userinfo['data']['userinfo'];
			setcookie('test_sig',serialize($userinfo));
		}
	}
}else{
	$userinfo = unserialize($_COOKIE['test_sig']);
}
if(stripos($assign['param'],'SigString') !== false && isset($userinfo['sig'])){
	$assign['param'] = str_ireplace('SigString',$userinfo['sig'],$assign['param']);
}

$tpl = new Tpl(Config::get('tpl.conf/web'));
$tpl->display('test.html',$assign);