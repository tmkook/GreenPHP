<?php
session_start();
require_once 'core/library/Loader.class.php';
Loader::addPath(dirname(__FILE__).'/core/extends/');
Loader::autoload();
Config::addPath(dirname(__FILE__).'/core/config/');
Url::setBaseUrl('./admin.php?');


$m = empty($_GET['m'])? 'mgr' : addslashes($_GET['m']);
$c = empty($_GET['c'])? 'index' : addslashes($_GET['c']);

if(empty($_SESSION['login_admin']) && $c != 'login'){
    Url::redirect('m=mgr&c=login');
}

require_once "apps/admin/{$m}/{$c}.php";