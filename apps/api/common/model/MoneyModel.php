<?php
/**
* 这是一个用户模块
*
* 这个模块封装user类,实现注册、登陆、重置密码、获取用户信息
*
* @package api
* @subpackage model
* @access protected
* @version $Id$
* @author $Author$
*/
class MoneyModel
{
	protected $table_users = 'users';
	protected $table_recharge = 'user_money_recharge';
	protected $db;
	protected $master_uin = 1;
	const MONEY_RATE = 100000;
	
	public function __construct() {
		$this->db = Database::connect(Config::get('db.conf/default'),true);
		$this->db->getPdo()->query('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;');
	}
	
	//事务开始
	public function begin(){
		static $begin;
		if(empty($begin)){
			$this->begin = 1;
			$this->db->getPDO()->beginTransaction();
		}
	}
	
	//回滚
	public function rollback(){
		$this->db->getPDO()->rollback();
	}
	
	//提交
	public function commit(){
		$this->db->getPDO()->commit();
	}
	
	//增加订单
	public function addTrade($param){
		$sign = $param['sign'];
		unset($param['sign']);
		$makeSign = $this->makesign($param);
		if($makeSign != $sign){
			throw new Exception('参数错误',101);
		}
		
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$param['tradeId']}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if( ! empty($trade_info)){
			return 1;
		}
		
		//获取用户渠道
		$userinfo = $this->db->from($this->table_users)->where('uin',$param['extraInfo'])->fetch();
		if(empty($userinfo['logplatform'])){
			$userinfo['logplatform'] = 'moredoo';
		}
		
		//记录订单
		$insert = array(
			'tradeId' => $param['tradeId'],
			'uin' => $param['extraInfo'],
			'platform' => $userinfo['logplatform'],
			'point' => (float)$param['point'],
			'amount' => (float)$param['amount'],
			'type' => empty($param['class'])? 'apple' : $param['class'],
			'datetime' => time(),
			'flag' => 1,
		);
		$id = $this->db->insertInto($this->table_recharge,$insert)->execute();
		if( ! $id){
			throw new Exception("记录订单失败",203);
		}
		//是否回调
		if($param['callback'] == 1){
			return array('id'=>$id);
		}else{
			$type = $insert['type'].'Callback';
			return $this->{$type}($param);
		}
	}
	
	//直接充值
	protected function Recharge($uin,$tradeId,$amount,$type){
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$tradeId}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if(empty($trade_info)){
			throw new Exception("订单不存在",202);
		}elseif($trade_info['flag'] == 0){
			$trade_info['balance'] = $this->get($trade_info['uin']);
			return $trade_info;
			//throw new Exception("订单已充值",201);
		}
		$uin = $trade_info['uin'];
		$product = $this->getProducts($amount);
		if(empty($product)){
			throw new Exception("商品不存在",203);
		}
		$recharge_money = $product['gamemoney'];
		$balance = $this->add((int)$uin,$recharge_money,'store','recharge',"{$type} 充值 {$recharge_money} 金币");
		$gift_money = $recharge_money * $product['add'];
		if($gift_money > 0){
			$balance = $this->add((int)$uin,$gift_money,'store','giveaway',"{$type} 充值赠送 {$gift_money} 金币");
		}
		$this->db->update($this->table_recharge)->set(array('flag'=>0,'amount'=>$amount))->where(array('tradeId'=>$tradeId))->execute(); //更新订单状态
		$trade_info['balance'] = $balance;
		return $trade_info;
	}

	//apple充值回调
	public function appleCallback($param){
		sleep(2);
		if(empty($param['uin'])) $param['uin'] = $param['extraInfo'];
		if(empty($param['tradeId']) || empty($param['amount']) || empty($param['uin'])){
			throw new Exception("参数错误",201);
		}
		//苹果订单验证
		$url = array('https://buy.itunes.apple.com/verifyReceipt','https://sandbox.itunes.apple.com/verifyReceipt');
		$check = Http::request($url[IS_DEV],json_encode(array('receipt-data'=>$param['receipt'])));
		$check = json_decode($check,true);
		$receipt = $check['receipt'];
		if(!isset($check['status']) || $check['status'] != 0 || $receipt['bid'] != 'coolcar.moredoo.com' || $param['tradeId'] != $receipt['transaction_id']){
			throw new Exception("支付失败",intval($check['status']));
		}
		return $this->Recharge($param['uin'],$param['tradeId'],$param['amount'],'apple');
	}

	//apple充值回调
	public function googleCallback($param){
		$productId = $param['productId'];
		$token = $param['receipt'];
		$verify_url = "https://www.googleapis.com/androidpublisher/v2/applications/air.moredoo.googlecar/purchases/products/{$productId}/tokens/{$token}";
		$data = json_decode(Http::request($verify_url,''),true);
		if(empty($data) || !empty($data['error'])){
			throw new Exception($data['error']['message'],$data['error']['code']);
		}
		if($data['consumptionState'] !== 0 && $data['purchaseState'] !== 0){
			throw new Exception("参数错误",201);
		}
		return $this->Recharge($param['uin'],$param['tradeId'],$param['amount'],'google');
	}
	
	//cake回调
	public function appcakeCallback($param){
		sleep(5);
		$token = 'tok_776bcf33cf3dc15fc5fd0cafe18670c04a9823ee';
		$appid = '31346937';
		$sign = str_replace("\n",'',str_replace('\n','',$param['sign']));
		$reqparam = array(
			'user_id' => $param['uin'],
			'amount' => $param['amount'],
			'order_serial' => $param['tradeId'],
		);
		ksort($reqparam);
		$makeSign = base64_encode(sha1(implode('-',$reqparam)."-{$token}"));
		if($makeSign != $sign){
			throw new Exception("参数错误",101);
		}
		return $this->Recharge($param['extraInfo'],$param['tradeId'],$param['amount'] / 100,'appcake');
	}
	
	//xy充值
	public function xyzspayCallback($param){
		$appkey = 'UEu9dcOlxe1DjD2HiI1v6kGMbJG2Vq2r';
		$reqparam = array(
			'orderid' => $param['tradeId'],
			'uid' => $param['uin'],
			'serverid' => $param['serverid'],
			'amount' => $param['amount'],
			'extra' => $param['extra'],
			'ts' => $param['ts'],
		);
		
		ksort($reqparam);
		$query_string = array();
		foreach ($reqparam as $key => $val){
			array_push($query_string, $key . '=' . $val);
		}
		$query_string = join('&', $query_string);
		$makeSign = md5($appkey . $query_string);
		$sign = $param['sign'];
		if($makeSign != $sign){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$param['tradeId']}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if(empty($trade_info)){
			unset($param['sign']);
			$param['callback'] = 1;
			$param['class'] = 'xyzspay';
			$param['extraInfo'] = $param['extra'];
			$param['sign'] = $this->makeSign($param);
			$this->addTrade($param);
		}
		$param['uin'] = $param['extra'];
		return $this->Recharge($param['uin'],$param['tradeId'],$param['amount'],'xyzs');
	}

	public function haimaCallback($param){
		error_reporting(0);
		$type='haimaios';
		$trans_data = $param['transdata'];
		$sign = $param['sign'];
		$key = 'NzBCODVEQzA1RkFGRDNFRjZDRkVFNkY1N0VDREFGNDYyMUFEMkRGN01USTVNekV3TXpBek9EY3lOamt5TlRVd05EY3JNVGd5TnpnM056QTNOalEyTWpVeU5EUTVNRFF5T1RrMk16STJNRGM1TWpFeU5qVXpOamt4';
		require dirname(__FILE__).'/IappDecrypt.class.php';
		$tools = new IappDecrypt();
		$result = $tools->validsign($trans_data,$sign,$key);
		if($result != 0){
			throw new Exception("参数错误",$result);
		}
		//充值
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$param['tradeId']}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if(empty($trade_info)){
			$trade_info['callback'] = 1;
			$trade_info['class'] = $type;
			$param['sign'] = $this->makeSign($param);
			$this->addTrade($param);
		}
		return $this->Recharge($param['extraInfo'],$param['tradeId'],$param['amount'],$type);
	}
	
	public function iappiosCallback($param){
		error_reporting(0);
		$type = 'iappios';
		$trans_data = $param['transdata'];
		$sign = $param['sign'];
		$key = 'QzNDMDJFOEUxQTZBQzI3QUM4MkEwRDFBMUFGMjgxOUUwNzJEQzEwQk1USTVPVFF4TURZM056UTJNemN5TVRZMk5EY3JNVFl4TlRRME16TTFNVEl3TVRBNE1qTXhORE15TXpBd05EVTNNalV6T1RjNE56azJOekUz';
		require dirname(__FILE__).'/IappDecrypt.class.php';
		$tools = new IappDecrypt();
		$result = $tools->validsign($trans_data,$sign,$key);
		if($result != 0){
			throw new Exception("参数错误",$result);
		}
		//充值
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$param['tradeId']}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if(empty($trade_info)){
			$trade_info['callback'] = 1;
			$trade_info['class'] = $type;
			$param['sign'] = $this->makeSign($param);
			$this->addTrade($param);
		}
		return $this->Recharge($param['extraInfo'],$param['tradeId'],$param['amount'],$type);
	}

	public function iappandCallback($param){
		error_reporting(0);
		$type = 'iappand';
		$trans_data = $param['transdata'];
		$sign = $param['sign'];
		$key = 'NzU0NDQ5REM4MzBDRTM0RTUyOTQwMjFEMzNBQzIxRURCMjRBRjdGQk1URTRNekkwTmpRMU16WTRPRE0wTkRFNE16a3JNVFEwTXprMU9EUTRPRFl4TlRjME5qY3lORFkwTURnMU5EQTJOemcxTmpBME56SXlNVGt4';
		require dirname(__FILE__).'/IappDecrypt.class.php';
		$tools = new IappDecrypt();
		$result = $tools->validsign($trans_data,$sign,$key);
		if($result != 0){
			throw new Exception("参数错误",$result);
		}
		
		//充值
		$sql = "SELECT * FROM {$this->table_recharge} WHERE tradeId='{$param['tradeId']}' FOR UPDATE";
		$trade_info = $this->db->getPdo()->query($sql)->fetch();
		if(empty($trade_info)){
			$param['callback'] = 1;
			$param['class'] = $type;
			unset($param['sign']);
			$param['sign'] = $this->makeSign($param);
			$this->addTrade($param);
		}
		return $this->Recharge($param['extraInfo'],$param['tradeId'],$param['amount'],$type);
	}

	protected function getProducts($money){
		Config::addPath(dirname(dirname(__FILE__))."/config/"); //载入模块配置
		$product = Config::get("lobby.conf/product");
		foreach($product as $key=>$val){
			if((float)$val['value'] == (float)$money){
				return $val;
			}
		}
	}
	
	protected function makesign($param){
		$token = 'bfb8384599b024dd0499251383353878';
		ksort($param);
		return md5(implode('-',$param).'-'.$token);
	}

	//加币
	public function add($uin,$sum,$reqapp,$action,$descr,$tax=0){
		$rst = $this->transfer(1,$uin,$sum,$reqapp,$action,$descr,$tax);
		return $rst['add_money'];
	}
	
	//减币
	public function reduce($uin,$sum,$reqapp,$action,$descr,$tax=0){
		$rst = $this->transfer($uin,1,$sum,$reqapp,$action,$descr,$tax);
		return $rst['reduce_money'];
	}
	
	//获取余额
	public function get($uin){
		if( ! is_numeric($uin) || $uin <= 0){
			throw new Exception("参数错误",101);
		}
		$sql = "SELECT money,money_verify FROM {$this->table_users} WHERE uin={$uin} FOR UPDATE";
		$query = $this->db->getPDO()->query($sql);
		$money = $query->fetch();
		if(empty($money)){
			throw new Exception("用户不存在",102);
		}
		if($money['money'] > 0 && $money['money_verify'] != $this->money_verify($money['money'],$uin)){
			throw new Exception("资金异常",102);
		}
		return $money['money'];
	}

	//转账
	protected function transfer($reduce_id,$add_id,$sum,$reqapp,$action,$descr,$tax=0){
		if($sum <= 0 || $reduce_id <= 0 || $add_id <= 0 || empty($reqapp) || empty($action) || empty($descr)){
			throw new Exception("参数错误",101);
		}
		$reduce_money = $this->get($reduce_id);
		if($reduce_money < $sum && $reduce_id == $this->master_uin){
			$reduce_money = $this->masterRecharge();
		}
		if($reduce_money < $sum){
			throw new Exception("金币余额不足",102);
		}
		$reduce_money -= $sum;
		$money_verify = $this->money_verify($reduce_money,$reduce_id);
		$sql = "UPDATE {$this->table_users} SET money={$reduce_money},money_verify='{$money_verify}' WHERE uin={$reduce_id}";
		$rst = $this->db->getPDO()->query($sql);
		if( ! $rst){
			throw new Exception("金币操作失败",103);
		}
		
		$add_money = $this->get($add_id);
		$add_money += $sum;
		$money_verify = $this->money_verify($add_money,$add_id);
		$sql = "UPDATE {$this->table_users} SET money={$add_money},money_verify='{$money_verify}' WHERE uin={$add_id}";
		$rst = $this->db->getPDO()->query($sql);
		if( ! $rst){
			throw new Exception("金币操作失败",104);
		}

		if($tax > 0){
			$uin = $add_id == 1? $reduce_id : $add_id;
			$insert= array('uin'=>$uin,'tax_money'=>$tax,'reqapp'=>$reqapp,'action'=>$action,'ts'=>time());
			$rst = $this->db->insertInto('user_tax_trades',$insert)->execute();
			if( ! $rst){
				throw new Exception("金币操作失败",105);
			}
		}

		//$reqapp = HttpQuery::extparam('reqapp');
		//$action = HttpQuery::extparam('action');
		$insert = array('reduce_id'=>$reduce_id,'reduce_balance'=>$reduce_money,'add_id'=>$add_id,'add_balance'=>$add_money,'money'=>$sum,'reqapp'=>$reqapp,'action'=>$action,'descr'=>$descr,'datetime'=>time());
		$rst = $this->db->insertInto('user_money_trades',$insert)->execute();
		if( ! $rst){
			throw new Exception("金币操作失败",106);
		}
		return array('reduce_money'=>$reduce_money,'add_money'=>$add_money);
	}
	
	public function getUserTrades($uin){
		if($uin <= 1){
			throw new Exception("uin号码不正确",101);
		}
		$result = array();
		$sql = "SELECT descr,datetime AS ts FROM user_money_trades WHERE (add_id={$uin} OR reduce_id={$uin}) AND (`action`='mgr_recharge' OR `action`='recharge' OR `action`='paygoods' OR `action`='cash_conversion') ORDER BY datetime DESC LIMIT 20";
		$data = $this->db->getPDO()->query($sql)->fetchAll();
		$sql = "SELECT money,goods,flag,datetime FROM user_exchange_goods WHERE uin={$uin} LIMIT 10";
		$goods = $this->db->getPDO()->query($sql)->fetchAll();
		$flag = array('已充值','待充值');
		foreach($goods as $key=>$val){
			$data[] = array('descr'=>$val['money'].'消费券兑换'.$val['goods'].'['.$flag[$val['flag']].']','ts'=>$val['datetime']);
		}
		
		//翻译
		if(isset($_SERVER['API_LANGUAGE'])){
			$lang = Config::get($_SERVER['API_LANGUAGE'].'.conf');
			foreach($data as $key=>$val){
				$data[$key]['descr'] = str_replace('充值',' '.$lang['充值'],$data[$key]['descr']);
				$data[$key]['descr'] = str_replace('金币',' '.$lang['金币'],$data[$key]['descr']);
				$data[$key]['descr'] = str_replace('消费券',' '.$lang['消费券'],$data[$key]['descr']);
				$data[$key]['descr'] = str_replace('兑换',' '.$lang['兑换'],$data[$key]['descr']);
				$data[$key]['descr'] = str_replace('已充值',' '.$lang['已充值'],$data[$key]['descr']);
				$data[$key]['descr'] = str_replace('未充值',' '.$lang['未充值'],$data[$key]['descr']);
			}
		}
		
		return $data;
	}
	
	//主账户充值
	private function masterRecharge(){
		//-------------------------------------------------------------------------------------
		//从发行账户中扣除
		$system_money = $this->db->getPdo()->query("SELECT * FROM `system_issue_money` WHERE id=1 LIMIT 1 FOR UPDATE")->fetch();
		if($system_money['balance'] < 1){
			//短信或邮件预警(发行余额已不足)
			throw new Exception("系统金币余额不足",101);
		}elseif($system_money['balance'] > 1 && $system_money['balance'] <= 1000000){
			//短信或邮件预警(发行余额已全部用完)
			$sum = $system_money['balance'];
		}else{
			$sum = $system_money['balance'];
		}
		$system_money['balance'] -= $sum;
		$system_money['reduce']  += $sum;
		$rst = $this->db->update('system_issue_money')->set($system_money)->where('id',1)->execute();
		if( ! $rst){
			//预警(发行账户操作失败)
			throw new Exception("金币操作失败",102);
		}
		//-------------------------------------------------------------------------------------
		
		$master_uin = $this->master_uin;
		$descr = '主账户充值';
		$balance = $this->get($master_uin) + $sum;
		$money_verify = $this->money_verify($balance,$master_uin);
		$sql = "UPDATE {$this->table_users} SET money={$balance},money_verify='{$money_verify}' WHERE uin={$master_uin}";
		$rst = $this->db->getPDO()->query($sql);
		if( ! $rst){
			throw new Exception("操作失败",103);
		}
		$insert = array('reduce_id'=>$master_uin,'reduce_balance'=>$balance,'add_id'=>$master_uin,'add_balance'=>$balance,'money'=>$sum,'reqapp'=>'php','action'=>'master_recharge','descr'=>$descr,'datetime'=>time());
		$rst = $this->db->insertInto('user_money_trades',$insert)->execute();
		if( ! $rst){
			throw new Exception("操作失败",104);
		}
		return $balance;
	}
	
    //user pwd
    private function money_verify($pwd,$uin){
        return md5(md5($pwd).md5(crc32($pwd)).$uin);
    }
    
}