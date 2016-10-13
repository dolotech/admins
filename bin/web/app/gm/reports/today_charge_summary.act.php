<?php
class Act_Today_charge_summary extends Page{     //记得改这里

	public function __construct(){
		parent::__construct();
		$sum_orders=0;
		$sum_players=0;
		$sum_players2=0;
		$sum_moneyGameAll=0;
		$sum_moneyAll=0;
		
		
	}

	public function process(){
		$servers = isset($this->input['servers']) ? $this->input['servers'] : array();
		//dump($servers);
		$data = array();
		foreach ($servers as $pid=>$si){
			foreach ($si as $sid=>$v){
		$data[$pid][$sid]=ChargeStat::sumDay($pid, $sid, 0, true);
			}
		}

		$this->assign('serv', $servers);
		$this->assign('data', $data);
		//dump($data);
		$this->display();
		
		
   }
}