<?php
class Act_Guild extends Page{     

    private
        $dbh,
        $limit = 30,
        $page = 0;

    public 
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
		$data = array();
		//$kw = $this->getKeyword();    //获得时间戳
		$sqlWhere = $this->getSqlWhere();   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.log_guild";
		$totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
		$data['list'] = $this->getList($sql . $sqlWhere);
		//var_dump($sql . $sqlWhere);
		$data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
		//$this->assign('kw', $kw);
		//var_dump($data);
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
	

		return $sqlWhere;
	
	}

}
