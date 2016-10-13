<?php
class Act_Charge_stat extends Page{     //记得改这里

	private
        $dbh,
        $db;
	public
	$platformid,
	$serverid;
	public function __construct(){
		parent::__construct();

		$this->setPlatformidServerid();
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->db = Db::getInstance();
		$this->assign('platformid', $this->platformid);
		$this->assign('serverid', $this->serverid);
	}

	public function process(){
		$yb1=0;
		$yb100=0;
		$yb200=0;
		$yb500=0;
		$yb1k=0;
		$yb2k=0;
		$yb5k=0;
		$yb1w=0;
		$yb2w=0;
		//查找内部号
		$localAccounts = $this->db->getAllCol1('select * from game_local_account;');
		$localAccountsStr = "'".implode("','", $localAccounts)."'";
		//dump($localAccountsStr);
		$local =" and account not in ({$localAccountsStr}) ";
		if($localAccounts==''){			
			$local = ' ';//防止没有内部号的时候sql语句出错
		}
		//dump($local);
		$sql = "SELECT sum(moneyGame) as yb FROM `log_charge_order` WHERE platformId ='{$this->platformid}' and serverId = '{$this->serverid}' {$local} group by account";
		//dump($sql);
		$arr=$this->db->getAllCol1($sql);//获得一列数据
		

		foreach($arr as $v){
			//echo $v;
			if(($v>=1)&&($v<=99)){
				$yb1+=1;
			}
			if(($v>=100)&&($v<=199)){
				$yb100+=1;
			}
			if(($v>=200)&&($v<=499)){
				$yb200+=1;
			}
			if(($v>=500)&&($v<=999)){
				$yb500+=1;
			}
			if(($v>=1000)&&($v<=1999)){
				$yb1k+=1;
			}
				if(($v>=2000)&&($v<=4999)){
				$yb2k+=1;
			}
				if(($v>=5000)&&($v<=9999)){
				$yb5k+=1;
			}
				if(($v>=10000)&&($v<=19999)){
				$yb1w+=1;
			}
				if($v>=20000){
				$yb2w+=1;
			}
			
		}
		
		//计算总人数
		$sum_er = $yb1+$yb100+$yb200+$yb500+$yb1k+$yb2k+$yb5k+$yb1w+$yb2w;
		//dump($sum_er);
		$this->assign('sum_er', $sum_er);
		$this->assign('yb1', $yb1);
		$this->assign('yb100', $yb100);
		$this->assign('yb200', $yb200);
		$this->assign('yb500', $yb500);
		$this->assign('yb1k', $yb1k);
		$this->assign('yb2k', $yb2k);
		$this->assign('yb5k', $yb5k);
		$this->assign('yb1w', $yb1w);
		$this->assign('yb2w', $yb2w);
		//dump($data);
		$this->display();
		
		
   }
}
