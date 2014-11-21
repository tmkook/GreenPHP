<?php
$assign = array();
$flag = array('禁止','正常');
$assign['realname'] = $_SESSION['login_admin']['realname'];
$assign['rolename'] = $_SESSION['login_admin']['rolename'];
$assign['platform'] = $_SESSION['login_admin']['platform'];
$assign['flag'] = $flag[$_SESSION['login_admin']['flag']];
$assign['createtime'] = date('Y年m月d日',$_SESSION['login_admin']['createtime']);
$assign['isdev'] = IS_DEV==1? '测试环境' : '正式环境';
$assign['lang'] = $_SERVER['API_LANGUAGE']? $_SERVER['API_LANGUAGE'] : 'cn';

function getChmod($filepath){
    return substr(base_convert(@fileperms($filepath),10,8),-4);
}

$assign['temp_chmod'] = getChmod(dirname(APPPATH).'/temp');
$assign['common_chmod'] = getChmod(APPPATH.'/api/common/config');
$assign['games_chmod'] = getChmod(APPPATH.'/api/games/config');
$assign['apache_version'] = apache_get_version();
$assign['errors'] = ini_get('display_errors');
$assign['memcache'] = $_SERVER['MEM_HOST'];
$assign['mongo'] = $_SERVER['MONGO_DB_HOST'];
$assign['mysql'] = $_SERVER['DB_HOST'];
$assign['localapi'] = $_SERVER['LOCAL_API_URL'];


if(empty($assign['platform'] )) $assign['platform']  = 'moredoo';
//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('welcome.html',$assign);