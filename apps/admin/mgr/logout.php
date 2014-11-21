<?php
//声明视图变量
$_SESSION['login_admin'] = array();
unset($_SESSION['login_admin']);
header('location:'.BASEURL.'/admin/mgr/login');