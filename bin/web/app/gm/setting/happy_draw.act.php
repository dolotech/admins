<?php
class Act_Happy_draw extends Page{   
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
		}elseif(isset($this->input['time_st'])&&isset($this->input['time_ed'])){
			$this->send();
		}
		$data = array(); 
		$sqlWhere = $this->getSqlWhere();   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.happy_draw";
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
		$sqlWhere .= isset($this->input['time_st']) && strlen($this->input['time_st']) ? " and  start_time = '{$this->input['time_st']}'" : '';
		$sqlWhere .= isset($this->input['time_ed']) && strlen($this->input['time_ed']) ? " and  start_end = '{$this->input['time_ed']}'" : '';
		return $sqlWhere;
	
	}
	
	private function send(){
		
			$time_st= strtotime($this->input['time_st']);
			$time_ed= strtotime($this->input['time_ed']);
			$this->dbh->query($sql = "INSERT INTO `happy_draw` (`start_time`,`end_time`) VALUES ('$time_st','$time_ed');");
		
			//var_dump("update invest_plan_data set name = '{$data['name']}' where id='{$id}'");
			
			$msg = "gmdrawhappy|$this->serverid;";//按照协议格式发送消息给服务器
			$rt = GmAction::send($msg, $this->serverid, $this->platformid);
			$url = Admin::url('happy_draw', '', '', true);//指定页面的地址
			//echo $url;
			if($rt['ret'] == 0){
				
				echo "<script>alert('OK');window.location.href='{$url}';</script>";
			}
		
			exit;		
		}
		private function del(){
			//dump($this->input);
			$id= $this->input['id'];
			$this->dbh->query($sql = "delete from happy_draw where id = '{$id}'");
			//echo $sql;
			$msg = "gmdrawhappy|$this->serverid";//按照协议格式发送消息给服务器
			$rt = GmAction::send($msg, $this->serverid, $this->platformid);
			$url = Admin::url('happy_draw', '', '', true);//指定页面的地址
			if($rt['ret'] == 0){
				
				echo "<script>alert('OK');window.location.href='{$url}';</script>";
			}			
			exit;
		}	

}