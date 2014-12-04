<?php
return array(
	'logs'=> array(
		'name' => '系统日志',
		'icon' => 'icon-list-alt',
		'childs' => array(
			'query_logs' => array('name'=>'PHP日志'),
		)
	),
	'manager'=> array(
		'name' => '系统管理',
		'icon' => 'icon-cog',
		'childs' => array(
			'admin_roles_edit' => array('name'=>'添加角色'),
			'admin_roles_list' => array('name'=>'角色管理'),
			'admin_user_add' => array('name'=>'添加帐号'),
			'admin_user_list' => array('name'=>'帐号列表'),
		)
	),
);
