<?php
class Act_Player_admin extends Page{   
	private
	$limit = 30,
	$page = 0;
	
	public
	$dbh,   //翻译id这里要用public属性
	$platformid,
	$serverid;
	public function __construct(){
		parent::__construct();
		// 设置平台ID和服务器ID：
		// 调用$this->setPlatformidServerid();
		// 会自动设置$platformid属性和$serverid属性
		$this->setPlatformidServerid();
		$this->input = trimArr($this->input);
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
		if(
				isset($this->input['page'])
				&& is_numeric($this->input['page'])
		){
			$this->page = $this->input['page'];   //这里把url上的page值赋给$page属性
		}
		$this->assign('serverid', $this->serverid);
		$this->assign('platformid', $this->platformid);

		
	}

	public function process(){
		if(isset($this->input['do'])
				&& $this->input['do'] == 'del'
		){
			$this->del();
		}elseif(isset($this->input['player_id'])){
			$this->send();
		}
		$data = array(); 
		$sqlWhere = $this->getSqlWhere();   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.log_player_admin";
		$totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
		$data['list'] = $this->getList($sql . $sqlWhere);
		//var_dump($sql . $sqlWhere);
		$data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
		$this->assign('kw', $kw);
		$this->assign('data', $data);
		
		
		$this->display();
		
	}
	

	/**
	 * 获取列表数据
	 * @param string $sql SQL查询字串
	 * @return array
	 */
	private function getList($sql)
	{
		$rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);
		$this->addParamCache(array('page'=>$this->page));
		$list = array();
		while ($row = $this->dbh->fetch_array($rs)) {
	
			$list[] = $row;
		}
		return $list;
	}
	
	/**
	 * 构造SQL where字串
	 * @param array $kw 搜索关键字
	 */
	private function getSqlWhere()
	{
		$sqlWhere = " where 1 ";
		$sqlWhere .= isset($this->input['id']) && strlen($this->input['id']) ? " and  player_id = '{$this->input['id']}'" : '';
		return $sqlWhere;
	
	}
	
	private function send(){
		
			$player_id= $this->input['player_id'];
			$this->dbh->query($sql = "INSERT INTO `log_player_admin` (`player_id`) VALUES ('$player_id' );");
		
			//var_dump("update invest_plan_data set name = '{$data['name']}' where id='{$id}'");
			
			$msg = "gmaddadmin|$player_id;";//按照协议格式发送消息给服务器
			$rt = GmAction::send($msg, $this->serverid, $this->platformid);
			$url = Admin::url('player_admin', '', '', true);//指定页面的地址
			//echo $url;
			if($rt['ret'] == 0){
				Admin::log(301, '设置了'.$player_id.'为新手指导员');
				echo "<script>alert('OK');window.location.href='{$url}';</script>";
			}
		
			exit;		
		}
		private function del(){
			$player_id = $this->input['player_id'];
			$this->dbh->query($sql = "delete from log_player_admin where player_id = '{$player_id}'");
			//echo $sql;
			$msg = "gmdeladmin|$player_id;";//按照协议格式发送消息给服务器
			$rt = GmAction::send($msg, $this->serverid, $this->platformid);
			$url = Admin::url('player_admin', '', '', true);//指定页面的地址
			if($rt['ret'] == 0){
				Admin::log(302, '取消了'.$player_id.'的新手指导员');
				echo "<script>alert('OK');window.location.href='{$url}';</script>";
			}			
			exit;
		}	

}