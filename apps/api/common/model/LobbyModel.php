<?php
/**
* 这是一个任务模块
*
* 这个模块封装Lobby类,实现获取押注排行、中奖排行
*
* @package api
* @subpackage model
* @access protected
* @version $Id$
* @author $Author$
*/
class LobbyModel
{
	protected $table_users  = 'users';
	protected $table_trades = 'user_money_trades';
	protected $table_goods  = 'user_exchange_goods';
	protected $table_msg = 'user_messages';
	protected $db;
	
	public function __construct() {
		$this->db = Database::connect(Config::get('db.conf/default'),true);
	}
	
	public function getConfig(){
		$conf = Config::get("lobby.conf");
		if(empty($conf['exchange_status'])){
			unset($conf['goods']);
		}
		return $conf;
	}
	
	//领取救济金
	public function getAlms($uin,$type){
		$alms = Config::get("lobby.conf/alms");
		$num = (int)$alms[0];
		$money = (int)$alms[1];
		$resttime = (int)$alms[2];
		//是否可领
		$is_reward = $this->queryAlms($uin,$type);
		if($is_reward['code'] != 100){
			throw new Exception($is_reward['desc'],$is_reward['code']);
		}
		//支付
		$rst = HttpQuery::api('/common/balance/sig_add','{"uin":"'.$uin.'","sum":'.$money.',"descr":"第'.$is_reward['getnum'].'次领取救济金"}',array('action'=>'alms'));
		if($rst['code'] != 100){
			throw new Exception($rst['desc'],$rst['code']);
		}
		return array('uin'=>$uin,'money'=>$money,'balance'=>$rst['data']['balance'],'num'=>$is_reward['num']-1,'resttime'=>$resttime);
	}
	
	//查询是否可以领救济金
	public function queryAlms($uin,$type){
		$alms = Config::get("lobby.conf/alms");
		$num = (int)$alms[0];
		$money = (int)$alms[1];
		$resttime = (int)$alms[2];
		//检查余额
		$rst = HttpQuery::api('/common/balance/get','{"uin":"'.$uin.'"}');
		if($rst['data']['balance'] >= $num * $money){
			return array('code'=>101,'desc'=>'您当前余额充足，还不能领取救济金');
		}
		
		$ts = time();
		$today = strtotime(date('Y-m-d 00:00:00'));
		$sql = "SELECT `datetime` FROM {$this->table_trades} WHERE add_id={$uin} AND `action`='alms' AND `datetime` BETWEEN {$today} AND {$ts} ORDER BY `datetime` DESC";
		$trades = $this->db->getPdo()->query($sql)->fetchAll();
		$getnum = count($trades);
		if(empty($trades[0]['datetime'])){
			$trades[0]['datetime'] = $ts-$resttime;
		}
		//已领完
		if($getnum >= $num){
			return array('code'=>101,'desc'=>'您今天的奖励已经领取完','getnum'=>$getnum);
		}
		//是否达到领取间隔时间
		$gettime =  $resttime - ($ts - $reward['ts']); //领取间隔时间
		if($gettime > 0){
			return array('code'=>102,'resttime'=>$gettime,'desc'=>"还没达到领取的时间",'getnum'=>$getnum);
		}
		return array('code'=>100,'resttime'=>0,'num'=>$num - $getnum);
		
	}

	
	//兑换历史
	public function getPaidGoods($uin){
		$goods = $this->db->from($this->table_goods)->where('uin',$uin)->fetchAll();
		return $goods;
	}
	
	//支付兑换
	public function payGoods($param){
		$uin = $param['uin'];
		$phone = $param['phone'];
		$qq  = $param['qq'];
		$goods = Config::get('lobby.conf/goods');
		$goods = $goods[$param['goods']];
		$money = $goods['money'];
		$name = $goods['name'];
		$validate = new Validate();
		if($goods['type'] == 'phone' && ! $validate->phone($phone)){
			throw new Exception("手机号码不正确",101);
		}elseif($goods['type'] == 'qb' && $qq < 10000){
			throw new Exception("QQ号码不正确",101);
		}
		$this->db->getPDO()->beginTransaction();
		$data = array(
			'uin'=>$uin,
			'phone' => $phone,
			'qq' => $qq,
			'goods'=>$name,
			'money'=>$money,
			'datetime' => time(),
		);
		$rst = $this->db->insertInto($this->table_goods,$data)->execute();
		if( ! $rst){
			$this->db->getPdo()->rollback();
			throw new Exception("兑换失败",102);
		}

		$pay = HttpQuery::api('/common/cash/sig_reduce','{"uin":"'.$uin.'","cash":'.$money.',"descr":"'.$param['goods'].'兑换商品 '.$name.'"}',array('action'=>'paygoods'));
		if($pay['code'] != 100){
			$this->db->getPdo()->rollback();
			throw new Exception($pay['desc'],$pay['code']);
		}
		$this->db->getPdo()->commit();
		$balance = HttpQuery::api('/common/balance/get','{"uin":"'.$uin.'"}');
		return $balance['data'];
	}


	//押注、中奖排行
	public function ranking($action ,$date) {
		$cache = Cache::connect(Config::get('cache.conf/common_mem'));
		$sig = 'rank_'.$action.md5(json_encode($date));
		$ranking = $cache->get($sig);
		if(empty($ranking)) {
			if($action == 'win') {
				$id = 'add_id';
				$extra = ' OR action="down"';
			} else {
				$id = 'reduce_id';
			}
				
			$sql = "SELECT {$id} as uin, SUM(money) AS summoney 
						FROM {$this->table_trades}
						WHERE {$id}!=1 and {$id} != 4501
							AND (reqapp='coolcar_1' OR reqapp='coolcar_2' OR reqapp='coolcar_3') 
							AND (action='{$action}'  {$extra})
							AND datetime BETWEEN {$date['start']} and  {$date['end']}
						GROUP BY {$id} 
						ORDER BY summoney DESC 
						LIMIT 100";
			$query = $this->db->getPDO()->query($sql);
			$ranking = $query->fetchall();
			if(empty($ranking)) return array();

			// 获取用户昵称
			$uinarr = array_map(
		        create_function('$element', 'return $element["uin"];'),
		        $ranking
			);
			$uinstr = implode(',', $uinarr);
			$sql1 = "SELECT uin, nickname, exp FROM users WHERE uin IN ({$uinstr}) ORDER BY FIELD(uin,{$uinstr});";
			$users = $this->db->getPDO()->query($sql1)->fetchall();
			foreach($ranking as $key=>$val){
				$ranking[$key]['nickname'] = $users[$key]['nickname'];
				$level = HttpQuery::api('/common/user/parse_level','{"exp":'. $users[$key]['exp'] .'}');
				$ranking[$key]['level'] = $level['data']['level'];
			}
			
			if(!empty($ranking)) $cache->set($sig, $ranking, $date['end']-$date['start']);
		}
		
		return (array)$ranking;
	}

	//增加用户奖励
	public function userRewardAdd($param){
		$uin      = $param['uin'];
		$descr = $param['descr'];
		$money =(int)$param['money'];
		$cash  =(int)$param['cash'];
	 if($uin<=0){
	  throw new Exception("参数错误",103);
	  }
	
		$data = array(
				'uin'=>$uin,
				'descr' => $descr,
				'money' => $money,
				'cash'=>$cash,
				'ts' => time(),
		);
		$rst = $this->db->insertInto('user_rewards',$data)->execute();
		if( ! $rst){
			throw new Exception("升级奖励失败",101);
		}
		return 1;		
	}
	
	//领取用户奖励
	public function userRewardGet($uin,$id){
	 if($uin<=0||$id<=0){
	  throw new Exception("参数错误",103);
	  }
	    $res=array();
		$row=$this->db->from('user_rewards')->where("uin={$uin} and id={$id}")->fetch();//获取用户奖励
   
		if($row&&$row['flag']==0){
			$this->db->getPDO()->beginTransaction();
			$rst=$this->db->update('user_rewards')->set('flag',1)->where("uin={$uin} and id={$id}")->execute(); //更改flag状态 1 已领取  0 未领取
			
			if($row['cash']>0){
				$param=array('uin'=>$uin,'cash'=>$row['cash'],'descr'=>"领取{$row['descr']}消费券");
			    $cash_rst = HttpQuery::api('/common/cash/sig_add',json_encode($param),array('action'=>'convercash'));  //增加消费劵
			       if($cash_rst['code']==100) $res['cash']=$cash_rst['data']['balance'];
		    	}
			if($row['money']>0){
	    		$param=array('uin'=>$uin,'sum'=>$row['money'],'descr'=>"领取{$row['descr']}金币");
			    $money_rst = HttpQuery::api('/common/balance/sig_add',json_encode($param),array('action'=>'moneycash'));//增加金币
			     if($money_rst['code']==100) $res['money']=$money_rst['data']['balance'];
		    	}	    	
					
		    	if($res){
		    		$res['reward']=array('id'=>$id,'money'=>$row['money'],'cash'=>$row['cash'],'descr'=>$row['descr']);
				   $this->db->getPdo()->commit();				
			    	return $res;			
		    	}
		    	$this->db->getPdo()->rollback(); 
					
		}else if($row&&$row['flag']==1){
			throw new Exception("奖励已领取",101);
		}else{ 
			throw new Exception("奖励不存在",102);
		}
		
	}
	
	//获取用户未领取的全部奖励
	public function userRewardList($uin){
	 if($uin<=0){
	   throw new Exception("参数错误",103);
	  }
		$sql="SELECT id,uin,descr,money,cash,ts FROM user_rewards where uin={$uin} and flag=0";
		$list= $this->db->getPdo()->query($sql)->fetchall();
		return $list;
	}
	
	/**
	 * 记录用户反馈信息
	 *
	 * @param string $type 类型
	 * @param string $content 反馈内容
	 * @return array 添加结果
	 */
	public function feedback($type,$content){
		if( empty($type)||empty($content)){
			throw new Exception("参数错误",103);
		}
		$values=array("type"=>$type,"content"=>$content,"created"=>time());
		$rst = $this->db->insertInto('feedback',$values)->execute();
		if( ! $rst){
			throw new Exception("操作失败",101);
		}
		return 1;
	}
	
	/**
	 * 更新用户历史消息
	 * 查询条件： 最近7天 信息类型等于message
	 * @return array
	 */
	public function getMsg(){
		$end = time();
		$start  = $end - 86400*7;
		$sql = "SELECT id,title,msg,ts FROM {$this->table_msg} WHERE type='message' AND ts BETWEEN {$start} AND {$end}";
		$list = $this->db->getPdo()->query($sql)->fetchAll();
		return $list;
	}
	
	/**
	 *更新用户msgid
	 *
	 * @param string $uin
	 * @param string $msgi  最后读取信息id
	 * @return array
	 */
	public function setMsgid($uin,$msgid){
		if($uin <= 0|| $msgid <= 0){
			throw new Exception("参数错误",103);
		}
		$rst = $this->db->update($this->table_users)->set("msgid",$msgid)->where('uin',$uin)->execute();
		if( ! $rst){
			throw new Exception("操作失败",101);
		}
		return 1;
	}
	
}