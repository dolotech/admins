<?php
class Act_Vip_stat extends Page{     //记得改这里

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
		$vip1=0;
		$vip2=0;
		$vip3=0;
		$vip4=0;
		$vip5=0;
		$vip6=0;
		$vip7=0;
		$vip8=0;
		$vip9=0;
		$vip10=0;
		//查找内部号
		$localAccounts = $this->db->getAllCol1('select * from game_local_account;');
		$localAccountsStr = "'".implode("','", $localAccounts)."'";
		//dump($localAccountsStr);
		$local =" and account not in ({$localAccountsStr}) ";
		if($localAccounts==''){			
			$local = ' ';//防止没有内部号的时候sql语句出错
		}
		$sql = "SELECT sum(moneyGame) as yb FROM `log_charge_order` WHERE platformId ='{$this->platformid}' and serverId = '{$this->serverid}' {$local} group by account";
		//dump($sql);
		$arr=$this->db->getAllCol1($sql);//获得一列数据
		

		foreach($arr as $v){
			//echo $v;
			if(($v>=200)&&($v<=999)){
				$vip1+=1;
			}
			if(($v>=1000)&&($v<=1999)){
				$vip2+=1;
			}
			if(($v>=2000)&&($v<=4999)){
				$vip3+=1;
			}
			if(($v>=5000)&&($v<=9999)){
				$vip4+=1;
			}
			if(($v>=10000)&&($v<=19999)){
				$vip5+=1;
			}
				if(($v>=20000)&&($v<=39999)){
				$vip6+=1;
			}
				if(($v>=40000)&&($v<=69999)){
				$vip7+=1;
			}
				if(($v>=70000)&&($v<=119999)){
				$vip8+=1;
			}
				if(($v>=120000)&&($v<=219999)){
				$vip9+=1;
			}
				if($v>=220000){
				$vip10+=1;
			}
	
			
			
		}
		
	

		
		//计算总人数
		$sum_er = $vip1+$vip2+$vip3+$vip4+$vip5+$vip6+$vip7+$vip8+$vip9+$vip10;
		//dump($sum_er);
		$this->assign('sum_er', $sum_er);
		$this->assign('vip1', $vip1);
		$this->assign('vip2', $vip2);
		$this->assign('vip3', $vip3);
		$this->assign('vip4', $vip4);
		$this->assign('vip5', $vip5);
		$this->assign('vip6', $vip6);
		$this->assign('vip7', $vip7);
		$this->assign('vip8', $vip8);
		$this->assign('vip9', $vip9);
		$this->assign('vip10', $vip10);
		//dump($data);
		$this->display();
		
		
   }
}
