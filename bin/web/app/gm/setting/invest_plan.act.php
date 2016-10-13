<?php
class Act_Invest_plan extends Page{    

	private
	$id,
	$dbh,  //$this->dbh 读取数据库
	$limit = 30,  //每页显示30条
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

        $type_arr = array(
        '1' => '坐骑普通投资',
        '2' => '坐骑豪华投资',
        '3' => '光翼普通投资',
        '4' => '光翼豪华投资',
        '5' => '海鳞普通投资',
        '6' => '海鳞豪华投资',
        '7' => '海钥普通投资',
        '8' => '海钥豪华投资',
        '9' => '天陨普通投资',
        '10' => '天陨豪华投资',
        '11' => '强化石普通投资',
        '12' => '强化石豪华投资'
        );
		
        $data = array();
        $kw = $this->getKeyword();    //获得时间戳
        $sqlWhere = $this->getSqlWhere($kw);   //获得where语句
        $sql = "select * from {$this->dbh->dbname}.invest_plan_data";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->assign('type_arr',$type_arr);
        //$this->assign('formAction', Admin::url('', '', '', true));
        $this->display();
		
	}
	
	/**
	 * 取得搜索关键字
	 * @return array
	 */
	private function getKeyword()
	{
		$kw = array();

		return $kw;
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
	private function getSqlWhere($kw)
	{
		$sqlWhere = " where 1";
	
		return $sqlWhere;
	
	}

}
