<?php
class Act_Sql_exe extends Page{     //记得改这里
	private
	$dbh;
	public
	$platformid,
	$serverid;
	public function __construct(){
		parent::__construct();

	
	}

	public function process(){
		//dump($this->input);//exit;
		$str='sqlgo123';
		$str=md5($str);
		$sqlnum=$this->input['sqlnum'];
		$sqlnum=md5($sqlnum);
		//dump($this->input);
		$servers = isset($this->input['servers']) ? $this->input['servers'] : array();
		$sql=$this->input['sql'];
		$sql=stripslashes($sql);//去掉sql语句中的反斜杠
		//var_dump($sql);
		if($sql){
		foreach ($servers as $pid => $si){
			foreach ($si as $sid =>$v){
		$this->dbh = GameDb::getGameDbInstance($pid, $sid);
		$this->dbh->exec($sql);


			}
		}
		echo "<script>alert('OK')</script>";
		 }
     	$this->assign('sqlnum', $sqlnum);
		$this->assign('serv', $servers);
		$this->assign('str', $str);
		$this->display();
		
		
   }
}