<?php
/**
* 这是一个任务模块
*
* 这个模块封装task类,实现获取任务列表、领取任务奖励、查询任务完成进度
*
* @package api
* @subpackage model
* @access protected
* @version $Id$
* @author $Author$
*/
class TaskModel
{
	protected $table_tasks = 'user_tasks';
	protected $table_status = 'user_tasks_status';
	protected $table_trades = 'user_money_trades';
	protected $table_friends = 'user_friends';
	protected $db;
	
	public function __construct() {
		$this->db = Database::connect(Config::get('db.conf/default'),true);
	}
	
	//获取任务列表
	public function getTasks($uin) {
		if($uin <= 0){
			throw new Exception("参数错误",101);
		}
		// 查询所有显示的任务(即包括uin已领取奖励和未领取奖励的任务)
		$sql = "SELECT t.type,t.id as taskid,t.icon,t.title,t.content,t.reward,t.go,s.ts,t.api
				FROM {$this->table_tasks} t LEFT JOIN {$this->table_status} s ON s.taskid = t.id and s.uin = {$uin}
				WHERE flag = 1";
		$query = $this->db->getPDO()->query($sql);
		$task = $query->fetchAll();
		$rst = array();
		foreach ($task as $key => $val) {
			//通过api，获取完成进度信息
			if($val['api']) {
				$params = json_decode($val['api'], true);
				$method = $params['type'];		
				unset($params['type']);	
				// accomplish = total 1、每日任务，当天已经领取过 2、新手任务，领取过 ，以上都不再重复查询
				if( ($params['tasktype'] == 'everyday' && $val['ts'] > strtotime(date('Y-m-d 00:00:00'))) || ($params['tasktype'] == 'once' && !empty($val['ts'])) ) {
					
					$schedule['data'] = ($params['total'] == 0)  ? array('accomplish' => 1,'total' => 1) : array('accomplish' => $params['total'],'total' => $params['total']);
				} else {
					$schedule['data'] = $this->{$method}($uin, json_encode($params));//HttpQuery::api($val['api'],'{"uin":"'.$uin.'"}');
					unset($schedule['data']['tasktype']);
				}				
			}

			$status = (!empty($val['ts']) && ($val['type'] == 2 || date('Y-m-d', $val['ts']) == date('Y-m-d'))) ? 1 : 0;
			$val = array_merge($val , array('schedule'  => $schedule['data']));
			$val = array_merge($val , array('status'  => $status));
			$type = $val['type'];
			unset($val['ts'],$val['api'],$val['type']);
			$rst[$type][] = $val;
		}
		return $rst;
	}
	
	//领取任务奖励
	public function getTasksReward($uin, $taskid) {
		if($uin <= 0 || $taskid <= 0){
			throw new Exception("参数错误",101);
		}		
		
		$sql = "SELECT id,type,reward,api FROM {$this->table_tasks} WHERE id = {$taskid} AND flag = 1 ";
		$query = $this->db->getPDO()->query($sql);
		$task = $query->fetch();
		if(empty($task)) {
			throw new Exception("任务不存在或已经过期", 102);
		}

		$sql1 = "SELECT * FROM {$this->table_status} WHERE uin = {$uin} and taskid = {$taskid}";
		$query1 = $this->db->getPDO()->query($sql1);
		$status = $query1->fetch();
		if($task['type'] == 1 && date('Y-m-d', $status['ts']) >= date('Y-m-d')) {
			throw new Exception("今日已经领取过奖励",102);
		} elseif ($task['type'] == 2 && $status['ts']) {
			throw new Exception("用户已经领取过奖励",102);
		}

		$params = json_decode($task['api'], true);
		$method = $params['type'];
		unset($params['type']);		
		$schedule = $this->{$method}($uin, json_encode($params));		
		if($schedule['accomplish'] < $schedule['total']) {
			throw new Exception("任务尚未完成", 102);
		}

		if(empty($status['ts'])) {
			$sql2="INSERT INTO {$this->table_status} values({$uin},{$taskid},{$task['reward']}," . time() . ")";
		} else {
			$sql2="UPDATE {$this->table_status} SET reward = {$task['reward']}, ts = " . time() . " where uin = {$uin} and taskid = {$taskid}";
		}		
		$rst = $this->db->getPdo()->query($sql2);		
		if(!$rst){
			throw new Exception("任务奖励领取失败",101);
		}
		$rst1 = HttpQuery::api('/common/balance/sig_add','{"uin":"'.$uin.'","sum":'.$task['reward'].',"descr":"task'.$taskid.'奖励 '.$task['reward'].'"}',array('action'=>'task'));
		if($rst1['code'] != 100){
			throw new Exception($rst1['desc'],$rst1['code']);
		}
		HttpQuery::imChatPush('{"cmd":300000,"uin":'.$uin.'}');
		return array('balance' => $rst1['data']['balance'], 'reward' => $task['reward']);
	}


	/********************************************任务完成条件查询 start****************************************************************/
	//查询$uin的押注、中奖次数、充值
	public function queryCounttrades($uin, $action, $date=array(), $reqapp=null) {
		if( ! is_numeric($uin) || $uin <= 0){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT COUNT(*) count FROM {$this->table_trades} WHERE  `action` = '{$action}' ";
		if($action == 'bet') {
			$sql .= " AND reduce_id = {$uin} ";
		} else {
			$sql .= " AND add_id = {$uin} ";
		}
		if( !empty($reqapp)) {
			$sql .= "AND reqapp = '{$reqapp}' ";
		}
		if( !empty($date)) {
			$sql .= "AND datetime BETWEEN {$date['start']} AND {$date['end']} ";
		}
		$query = $this->db->getPDO()->query($sql);
		$count = $query->fetch();
		
		return array('count' => $count['count']);
	}

	//查询$uin的押注、充值总额
	public function querySumtrades($uin, $action, $date=array(), $reqapp=null) {
		if( ! is_numeric($uin) || $uin <= 0){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT SUM(money) summoney FROM {$this->table_trades} WHERE  `action` = '{$action}' ";
		if($action == 'bet') {
			$sql .= " AND reduce_id = {$uin} ";
		} else {
			$sql .= " AND add_id = {$uin} ";
		}
		if( !empty($reqapp)) {
			$sql .= "AND reqapp = '{$reqapp}' ";
		}
		if( !empty($date)) {
			$sql .= "AND datetime BETWEEN {$date['start']} AND {$date['end']} ";
		}
		$query = $this->db->getPDO()->query($sql);
		$money = $query->fetch();		

		return array('summoney' => $money['summoney']);
	}

	//查询$uin的好友个数
	public function queryCountfriends($uin) {
		if( ! is_numeric($uin) || $uin <= 0){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT COUNT(*) count FROM {$this->table_friends} WHERE uin = {$uin} and flag = 1 and friend <> 10000";// friend = 10000,系统自动添加的好友，需要剔除
		$query = $this->db->getPDO()->query($sql);
		$count = $query->fetch();

		return array('count' => $count['count']);
	}
	/********************************************任务完成条件查询 end****************************************************************/

	public function countrecharge($uin, $params) {		
		$params = json_decode($params,true);
		$date = ($params['tasktype'] == 'everyday') ? array('start' => strtotime(date('Y-m-d 00:00:00')), 'end' => time()) : array();		
		$recharge = $this->queryCounttrades($uin, 'recharge', $date);	
		$accomplish = ($recharge['count'] > $params['total']) ? $params['total'] : $recharge['count'];
		if($params['total'] == 0) $params['total'] = $accomplish = 1;//新手教学内容
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}

	public function countbet($uin, $params) {		
		$params = json_decode($params,true);
		$date = ($params['tasktype'] == 'everyday') ? array('start' => strtotime(date('Y-m-d 00:00:00')), 'end' => time()) : array();
		$bet = $this->queryCounttrades($uin, 'bet', $date, $params['level']);			
		$accomplish = ($bet['count'] > $params['total']) ? $params['total'] : $bet['count'];		
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}	

	public function sumbet($uin, $params) {		
		$params = json_decode($params,true);
		$date = ($params['tasktype'] == 'everyday') ? array('start' => strtotime(date('Y-m-d 00:00:00')), 'end' => time()) : array();	
		$money = $this->querySumtrades($uin, 'bet', $date, $params['level']);	
		$accomplish = (floor($money['summoney']/10000) > $params['total']) ? $params['total'] : floor($money['summoney']/10000);
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}

	public function friends($uin, $params) {
		$params = json_decode($params,true);
		$friends = $this->queryCountfriends($uin);
		$accomplish = ($friends['count'] > $params['total']) ? $params['total'] : $friends['count'];	
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}

	public function countwin($uin, $params) {
		$params = json_decode($params,true);
		$date = ($params['tasktype'] == 'everyday') ? array('start' => strtotime(date('Y-m-d 00:00:00')), 'end' => time()) : array();	
		$win = $this->queryCounttrades($uin, 'win', $date, $params['level']);
		$accomplish = ($win['count'] > $params['total']) ? $params['total'] : $win['count'];
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}

	public function sumwin($uin, $params) {
		$params = json_decode($params,true);
		$date = ($params['tasktype'] == 'everyday') ? array('start' => strtotime(date('Y-m-d 00:00:00')), 'end' => time()) : array();	
		$money = $this->querySumtrades($uin, 'win', $date, $params['level']);
		$accomplish = (floor($money['summoney']/10000) > $params['total']) ? $params['total'] : floor($money['summoney']/10000);
		return array(
				'tasktype' => $params['tasktype'],
				'accomplish' => (int)$accomplish,
				'total' => (int)$params['total']
			);
	}
}