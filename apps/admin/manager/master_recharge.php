<?php
$db = Database::connect(Config::get('db.conf/default'));
//$db->debug = true;
$system_money = $db->getPdo()->query("SELECT * FROM system_issue_money WHERE id=1 LIMIT 1 FOR UPDATE")->fetch();
if(HttpQuery::isAjax()){
	$vali = array(
		array(
			'field'=>'money',
			'label'=>'金额',
			'rules'=> array('required','integer')
		),
		array(
			'field'=>'descr',
			'label'=>'描述',
			'rules'=> array('required')
		),
	);
	$validate = new Validate();
	if( ! $validate->run($_POST,$vali)){
		exit('{"code":"error","desc":"'.$validate->getMessage().'"}');
	}
	if($_POST['money'] < 0 && abs($_POST['money']) > $system_money['balance']){
		exit('{"code":"error","desc":"扣除金额不能大于系统余额"}');
	}
	$system_money['balance'] += intval($_POST['money']);
	$system_money['descr'] = $_POST['descr']." 金额：{$_POST['money']}";
	$system_money['ts'] = time();
	
	$rst = $db->update('system_issue_money')->set($system_money)->where('id',1)->execute();
	if ($rst) {
		exit('{"code":"success","desc":"操作成功！"}');
	} else {
		exit('{"code":"error","desc":"更新失败！"}');
	}
}

//账号 1 的余额
$rst_1 = HttpQuery::api('/common/balance/get','{"uin":"1"}',array("reqapp"=>"master_recharge"));
$rst_2 = HttpQuery::api('/common/balance/get','{"uin":"4501"}',array("reqapp"=>"master_recharge"));
$users_money = $db->getPdo()->query("SELECT SUM(money) FROM users WHERE uin != 1 AND uin != 4501")->fetchColumn();
$users_cash = $db->getPdo()->query("SELECT SUM(cash) FROM user_cash_coupons WHERE uin != 1")->fetchColumn();
$assign = array();
$assign['balance'] = $rst_1['data']['balance'];
$assign['robot_money'] = $rst_2['data']['balance'];
$assign['system_money'] = $system_money;
$assign['users_money'] = $users_money;
$assign['users_cash'] = $users_cash;

$tpl = new Tpl(Config::get('tpl.conf/admin'));
$tpl->display('master_recharge.html', $assign);

