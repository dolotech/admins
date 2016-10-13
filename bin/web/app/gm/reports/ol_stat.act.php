<?php
class Act_Ol_stat extends Page{     //记得改这里

	private
	$dbh;
	public
	$platformid,
	$serverid;
	public function __construct(){
		parent::__construct();

		$this->setPlatformidServerid();
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
		$this->assign('platformid', $this->platformid);
		$this->assign('serverid', $this->serverid);
	}

	public function process(){
		$time_st = today0clock();
		$time_ed = today0clock()+24*3600-1;
		$i_1=0;
		$i_30=0;
		$i_60=0;
		$i_120=0;
		$i_240=0;
		
		$sql = "SELECT sum(last) as 'ol_time' FROM `log_in_out` WHERE 1 and time>={$time_st} and time<={$time_ed} group by player_id ";
		$arr=$this->dbh->getAllCol1($sql);
		
// 		$sql2 = "select count(player_id) as 'num' from `log_in_out` where 1 and time>={$time_st} and time<={$time_ed} group by player_id ";
// 		$sum_er = $this->dbh->getRow($sql2);
// 		$sql = "SELECT sum(last) as 'ol_time' FROM `log_in_out` WHERE 1   group by player_id ";
// 		$arr=$this->dbh->getAllCol1($sql);
		
		//dump($arr);
		//echo count($arr);
		//dump($this->input);


		foreach($arr as $v){
			//echo $v;
			$v=$v/60;
			if(($v>=1)&&($v<=30)){
				$i_1+=1;
			}
			if(($v>30)&&($v<=60)){
				$i_30+=1;
			}
			if(($v>60)&&($v<=120)){
				$i_60+=1;
			}
			if(($v>120)&&($v<=240)){
				$i_120+=1;
			}
			if($v>240){
				$i_240+=1;
			}
			
		}
		
	

		
		//计算总人数
		$sum_er = $i_1+$i_30+$i_60+$i_120+$i_240;
		//dump($sum_er);
		$this->assign('sum_er', $sum_er);
		$this->assign('i_1', $i_1);
		$this->assign('i_30', $i_30);
		$this->assign('i_60', $i_60);
		$this->assign('i_120', $i_120);
		$this->assign('i_240', $i_240);
		//dump($data);
		$this->display();
		
		
   }
}