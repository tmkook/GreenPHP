<?php
class AdminRoles
{
	protected $table = 'admin_users_roles';
	protected $db;
	public function __construct(){
		Config::addPath(dirname(dirname(dirname(__FILE__))).'/source/_lang/');
		//$this->db = Database::connect(Config::get('db.conf/default'));
	}
	
	public function getUserRole($uid){
		if($uid == 1){
			return array('rolename'=>'超级管理','access'=>'all');
		}
		$sql = "SELECT `rolename`,`access` FROM admin_users AS users,admin_users_roles AS roles WHERE users.id={$uid} AND users.role=roles.id LIMIT 1";
		$db = Database::connect(Config::get('db.conf/default'));
		$role = $db->getPdo()->query($sql)->fetch();
		$role['access'] = json_decode($role['access'],true);
		return $role;
	}
	
	public function getUserMenus($uid){
		$menus = $this->getAllMenus();
		if($uid == 1){
			return $menus;
		}
		$role = $this->getUserRole($uid);
		$user_menus = array();
		foreach($menus as $pn=>$parent){
			if(!isset($role['access'][$pn])){
				unset($menus[$pn]);
			}else{
				foreach($parent['childs'] as $cn=>$child){
					if( ! in_array($cn,$role['access'][$pn])){
						unset($menus[$pn]['childs'][$cn]);
					}
				}
			}
		}
		return $menus;
	}
	
	
	public function getAllMenus(){
		$menus = $lang = Config::get('admin_menus_zh_cn');
		$dirs = glob(APPPATH.'/admin/*');
		foreach($dirs as $dir){
			if(is_dir($dir)){
				$parent =  basename($dir);
				if(empty($menus[$parent]['name'])) $menus[$parent]['name'] = $parent;
				if(empty($menus[$parent]['icon'])) $menus[$parent]['icon'] = 'icon-chevron-right';
			}
			$files = glob($dir.'/*.php');
			foreach($files as $filename){
				$child = current(explode('.',basename($filename)));
				if(empty($menus[$parent]['childs'][$child]['name'])){
					$menus[$parent]['childs'][$child]['name'] = $child;
				}
			}
		}
		unset($menus['mgr'],$menus['monitor']);
		return $menus;
	}
	
}