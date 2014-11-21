<?php
return array(
	'users'=> array(
		'name' => '用户管理',
		'icon' => 'icon-user',
		'childs' => array(
			'user_list' => array('name'=>'用户列表'),
			'user_exchange_goods' => array('name'=>'兑换商品'),
			'user_money_recharge' => array('name'=>'充值流水'),
			'user_money_trades' => array('name'=>'交易流水'),
			'user_cash_trades' => array('name'=>'消费券流水'),
			'user_cash_coupons' => array('name'=>'消费券余额'),
		)
	),
	'operation'=> array(
		'name' => '运营管理',
		'icon' => 'icon-briefcase',
		'childs' => array(
			'operationdata' => array('name'=>'运营数据'),
			'system_msg' => array('name'=>'消息发布'),
			'msg_list' => array('name'=>'消息列表' ),
			'feedback' => array('name'=>'反馈信息' ),
			'businessdata' => array('name'=>'业务数据'),
			'currentonlinetime'=> array('name'=>'在线用户'),
			'pastonlinetime'=> array('name'=>'历史时长'),
			'pastonlineuser'=> array('name'=>'历史登陆'),
			//'versioninfo' => array('name'=>'版本查询' ),
			'setversion' => array('name'=>'版本配置' ),
			'task_list' => array('name'=>'任务列表'),
			'tasks_edit' => array('name'=>'配置任务'),
			'userstatistics' => array('name' => '用户统计'),
			'avgonlinetime' => array('name' => '在线时长'),
			'rechargestatistics' => array('name' => '付费用户'),
			'download_url' => array('name'=>'下载配置'),
			'download_url_list' => array('name'=>'下载列表'),
			'money_recharge' => array('name'=>'金币充值' ),
			'cash_recharge' => array('name'=>'消费券充值' ),
			'clientsummaryinfo' => array('name'=>'客户端信息'),
			'retentionrate' => array('name' => '用户留存率'),
			'blacklist' => array('name'=>'黑名单管理'),
			'cron_list' => array('name'=>'离线消息列表'),
			'crons_edit' => array('name'=>'离线消息配置'),
		)
	),
	'devops'=> array(
		'name' => '运维管理',
		'icon' => 'icon-hdd',
		'childs' => array(
			'developmentdata' => array('name'=>'运维数据'),
			'dbreqinfo' => array('name'=>'接口效率'),
			'logininfo' => array('name'=>'登陆信息'),
			'key_resources' => array('name'=>'密钥资源'),
			'resourcemanage' => array('name'=>'资源管理'),
			'blackip_list' => array('name'=>'IP黑名单'),
			'servercontrol' => array('name'=>'服务器控制'),
			'servercontrol_list' => array('name'=>'服务器列表'),
			'proxyserver' => array('name'=>'代理服务器'),
			'serverinfo' => array('name'=>'服务器信息'),
			//'webversioninfo' => array('name'=>'flash版本配置'),
			'setproxyserver' => array('name'=>'配置代理服务器'),
		)
	),
	'logs'=> array(
		'name' => '系统日志',
		'icon' => 'icon-list-alt',
		'childs' => array(
			'query_logs' => array('name'=>'PHP日志'),
			'exception_log' => array('name'=>'服务日志'),
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
			'lobby_config' => array('name'=>'大厅配置'),
			'master_recharge' => array('name'=>'虚拟币发行'),
		)
	),
	'games'=> array(
		'name' => '酷车管理',
		'icon' => 'icon-th',
		'childs' => array(
			'game_coolcar' => array('name'=>'酷车配置'),
			'open_running' => array('name'=>'开奖流水'),
			'daily_trade_summary' => array('name'=>'交易日汇总'),
			'user_money_trades' => array('name'=>'交易流水'),
			'bet_summary' => array('name' => '押注汇总'),
			'win_summary' => array('name' => '中奖汇总'),
			'win_bet_summary' => array('name' => '赢的汇总'),
			'manager_win_lose' => array('name' => '店长输赢'),
			'win_percentage' => array('name' => '车牌概率'),
			'user_data' => array('name' => '综合数据'),
			'summary' => array('name' => '统计'),
		)
	),
	'backstage'=> array(
		'name' => '构建后台',
		'icon' => 'icon-road',
		'childs' => array(
			// 'main_project_manage' => array('name'=>'管理主项目'),
			'main_project_compile' => array('name'=>'编译主项目'),
			'deputy_project_compile' => array('name'=>'编译副项目'),
			// 'program_remove' => array('name'=>'删除项目'),
			'program_tree' => array('name'=>'项目依赖树'),
			// 'program_info' => array('name'=>'项目信息'),
			),
		),
);
