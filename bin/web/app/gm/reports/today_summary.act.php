<?php
class Act_Today_summary extends Page{     //记得改这里
	public
	$dbh,
	$platformid,
	$serverid,
	$db;

	public function __construct(){
		parent::__construct();
		$this->setPlatformidServerid();
		//$this->input = trimArr($this->input);
		$this->db = Db::getInstance();//连接到game数据库
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);

		$this->assign('serverid', $this->serverid);
		$this->assign('platformid', $this->platformid);
		
	}

	public function process(){
		$timeStart = today0clock();
		$timeEnd = today0clock()+3600*24-1;
		$sqlreg = "select count(userid) from log_cp_user where regtime >= {$timeStart} and regtime <= {$timeEnd}";
		$regsum = $this->dbh->getOne($sqlreg);//今天注册人数
		//dump($regsum);
		
		$sqlrole = "select count(player_id) from log_create_role where time >={$timeStart} and time <= {$timeEnd}";
		$createsum = $this->dbh->getOne($sqlrole);//今天创角人数

		$sqlonline = "select count from log_online order by time desc limit 1";
		$nowol = $this->dbh->getOne($sqlonline);//当前在线人数
		
		$sqlonline = "select MAX(count) from log_online where time >={$timeStart} and time <= {$timeEnd}";//计算今天最大在线人数
		$olsum = $this->dbh->getOne($sqlonline);//今天最高在线人数
		
		$sqlcharge = "select count(DISTINCT account) from log_charge_order where platformId = '{$this->platformid}' and serverId = {$this->serverid}  and ctime >={$timeStart} and ctime <= {$timeEnd}";
		$chargesum = $this->db->getOne($sqlcharge);//今天付费人数
		
		$sqlcharge2 = "select count(DISTINCT account) from log_charge_order where platformId = '{$this->platformid}' and serverId = {$this->serverid} and isFirst = 0 and ctime >={$timeStart} and ctime <= {$timeEnd}";
		$charge2sum = $this->db->getOne($sqlcharge2);//今天二次付费人数
		
		$sqlmoney = "select sum(money) from log_charge_order where platformId = '{$this->platformid}' and serverId = {$this->serverid} and ctime >={$timeStart} and ctime <= {$timeEnd}";
		$moneysum = $this->db->getOne($sqlmoney);//今天总收入(分)
		
		$this->assign('regsum', $regsum);
		$this->assign('createsum', $createsum);
		$this->assign('nowol', $nowol);
		$this->assign('olsum', $olsum);
		$this->assign('chargesum', $chargesum);
		$this->assign('charge2sum', $charge2sum);
		$this->assign('moneysum', $moneysum);
		$this->display();
		
	}

}