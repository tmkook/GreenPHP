<?php
/**
* 这是一个现金券模块
*
* 这个模块封装CashCoupon类,实现增加消费券、扣除消费券、金币与消费券之间的兑换
*
* @package api
* @subpackage model
* @access protected
* @version $Id$
* @author $Author$
*/
class CashModel
{
	protected $table_coupons = 'user_cash_coupons';
	protected $table_cash = 'user_cash_trades';
	protected $db;
	protected $master_uin = 1;
	public function __construct() {
		$this->db = Database::connect(Config::get('db.conf/default'),true);
	}

	//获取现金券余额
	public function get($uin){
		if( $uin <= 0){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT cash,money_verify FROM {$this->table_coupons} WHERE uin={$uin}";
		$query = $this->db->getPDO()->query($sql);
		$money = $query->fetch();
		if(empty($money)){
			return 0;
		}
		if($money['cash'] > 0 && $money['money_verify'] != $this->money_verify($money['cash'],$uin)){
			throw new Exception("消费券余额异常",102);
		}
		return $money['cash'];
	}
	
	// 金币兑换消费券,比率 1000 ： 1
	public function conversion_to_cash($uin, $cash, $reqapp, $action, $descr){
		if($uin <= 0 || $cash <= 0 || empty($action) || empty($descr)){
			throw new Exception("参数错误",101);
		}

		// 增加消费券
		$rst = $this->add($uin,$cash,$reqapp,'conversion_to_cash',$descr);

		// 从账号1扣除金币 : user_money_trades
		$money = $cash * 1000;
		$rst1 = HttpQuery::api('/common/balance/sig_reduce','{"uin":"'.$uin.'","sum":'.$money.',"descr":"'.$descr.', 1000金币兑换1消费券"}',array('action'=>'cash_conversion'));
		if($rst1['code'] != 100){
			throw new Exception($rst1['desc'],$rst1['code']);
		}
		
		return array('balance' => $rst['balance'], 'money' => $rst1['data']['balance']);
	}

	// 消费券兑换金币,比率 1 ：1000 每天限制兑换2次（memcache存储）
	public function conversion_to_money($uin, $cash, $reqapp, $action, $descr){
		if($uin <= 0 || $cash <= 0 || empty($action) || empty($descr)){
			throw new Exception("参数错误",101);
		}

		$cache = Cache::connect(Config::get('cache.conf/common_mem'));
		$sig = md5('conversion_to_money_' . $uin . strtotime(date('Y-m-d')));
		$conversion_count = $cache->get($sig);
		$conversion_count = empty($conversion_count) ? 0 : $conversion_count;
		if($conversion_count >=2) {
			throw new Exception("当天兑换次数已用完", 102);
		}

		// 扣消费券
		$rst = $this->reduce($uin,$cash,$reqapp,'conversion_to_money',$descr);

		// 从账号1增加金币 : user_money_trades
		$money = $cash * 1000;
		$rst1 = HttpQuery::api('/common/balance/sig_add','{"uin":"'.$uin.'","sum":'.$money.',"descr":"'.$descr.', 1消费券兑换1000金币"}',array('action'=>'cash_conversion'));
		if($rst1['code'] != 100){
			throw new Exception($rst1['desc'],$rst1['code']);
		}
		
		$cache->set($sig, $conversion_count+1, (strtotime(date('Y-m-d') . '+1 day') - time()));
		
		return array('balance' => $rst['balance'], 'money' => $rst1['data']['balance']);
	}

	//加消费券
	public function add($uin,$add_cash,$reqapp,$action,$descr){
		if($uin <= 0 || $add_cash <= 0 || empty($reqapp) || empty($action) || empty($descr)){
			throw new Exception("参数错误",101);
		}
		// 从账号1扣除金币 : user_money_trades
		// $money = $cash * 1000;
		// $rst1 = HttpQuery::api('/common/balance/sig_reduce','{"uin":"'.$uin.'","sum":'.$money.',"descr":"'.$descr.'"}',array('action'=>'addcash'));
		// if($rst1['code'] != 100){
		// 	throw new Exception($rst1['desc'],$rst1['code']);
		// }		

		// 增加消费券 ： user_cash_coupons
		$cash = $this->get($uin);				
		$cash += $add_cash;
		$money_verify = $this->money_verify($cash,$uin);
		$sql = "INSERT {$this->table_coupons} (uin, cash, money_verify) values ({$uin}, {$add_cash}, '{$money_verify}') ON DUPLICATE KEY UPDATE cash={$cash},money_verify='{$money_verify}'";
		$rst = $this->db->getPDO()->query($sql);
		if( ! $rst){
			throw new Exception("消费券操作失败",102);
		}		

		// 写入user_cash_trades	
		$insert = array('reduce_id'=>1,'reduce_balance'=>0,'add_id'=>$uin,'add_balance'=>$cash,'money'=>$add_cash,'reqapp'=>$reqapp,'action'=>$action,'descr'=>$descr,'datetime'=>time());	
		$rst1 = $this->db->insertInto($this->table_cash, $insert)->execute();
		if( ! $rst1){
			throw new Exception("消费券操作失败",103);
		}

		return array('balance' => $cash);
	}
	
	//减消费券
	public function reduce($uin,$reduce_cash,$reqapp,$action,$descr){
		if($uin <= 0 || $reduce_cash <= 0 || empty($reqapp) || empty($action) || empty($descr)){
			throw new Exception("参数错误",101);
		}
		// 从账号1增加金币 : user_money_trades
		// $money = $cash * 1000;
		// $rst1 = HttpQuery::api('/common/balance/sig_add','{"uin":"'.$uin.'","sum":'.$money.',"descr":"'.$descr.'"}',array('action'=>'reducecash'));
		// if($rst1['code'] != 100){
		// 	throw new Exception($rst1['desc'],$rst1['code']);
		// }

		// 扣除消费券 ： user_cash_coupons
		$cash = $this->get($uin);
		if($cash < $reduce_cash){
			throw new Exception("消费券余额不足",102);
		}
		$cash -= $reduce_cash;
		$money_verify = $this->money_verify($cash,$uin);
		$sql = "UPDATE {$this->table_coupons} SET cash={$cash},money_verify='{$money_verify}' WHERE uin={$uin}";			
		$rst = $this->db->getPDO()->query($sql);
		if( ! $rst){
			throw new Exception("消费券操作失败",102);
		}			

		// 写入user_cash_trades	
		$insert = array('reduce_id'=>$uin,'reduce_balance'=>$cash,'add_id'=>1,'add_balance'=>0,'money'=>$reduce_cash,'reqapp'=>$reqapp,'action'=>$action,'descr'=>$descr,'datetime'=>time());
		$rst1 = $this->db->insertInto($this->table_cash, $insert)->execute();
		if( ! $rst1){
			throw new Exception("消费券操作失败",103);
		}

		return array('balance' => $cash);
	}

	//user pwd
    private function money_verify($pwd,$uin){
        return md5(md5($pwd).md5(crc32($pwd)).$uin);
    }
}