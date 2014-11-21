<?php
$roles = new AdminRoles();
$menus = $roles->getUserMenus($_SESSION['login_admin']['id']);
$assign = $search_uri = $search_word = array();

foreach($menus as $m=>$parent){
	foreach($parent['childs'] as $c=>$child){
		$menus[$m]['childs'][$c]['uri'] = $m.'/'.$c;
		$search_word[] = $child['name'];
		$search_uri[] = $m.'/'.$c;
	}
}

$assign['menus'] = $menus;
$assign['search_word'] = json_encode($search_word);
$assign['search_uri']  = json_encode($search_uri);

//加载视图
$view = new Tpl(Config::get('tpl.conf/admin'));
$view->display('index.html',$assign);