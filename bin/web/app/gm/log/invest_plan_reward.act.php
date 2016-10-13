<?php
class Act_Invest_plan_reward extends Page{

	private

        $limit = 30,
        $page = 0;

    public 
    	$dbh,//翻译id需要用公共属性
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
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
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
		$kw = $this->getKeyword();   
		//dump($kw);//返回的结果是一个数组 
		$sqlWhere = $this->getSqlWhere($kw);   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.log_invest_plan";
		$totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
		$data['list'] = $this->getList($sql . $sqlWhere);
		$data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
		$this->assign('kw', $kw);//搜索后保留搜索关键字
		$this->assign('data', $data);
        $this->assign('type_arr',$type_arr);
		//$this->assign('formAction', Admin::url('', '', '', true));
		$this->display();
		//var_dump($sql.$sqlWhere);
	}
	
	/**
	 * 取得搜索关键字
	 * @return array
	 */
	private function getKeyword()
	{
		$kw = array();

		if (($this->input['kw']['player_id']))
		{
			$kw['kw_player_id'] = $this->input['kw']['player_id'];
		}
        if (($this->input['kw']['player_name']))
        {
            $kw['kw_player_name'] = $this->input['kw']['player_name'];
        }

		if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
		{
			$kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);     //获得开始时间戳
			$kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);     //获得结束时间戳
		}
		return $kw;
	}

	/**
	 * 获取列表数据
	 * @param string $sql SQL查询字串
	 * @return array
	 */					//这里$sql的名字可以任取
	private function getList($sql)
	{
		//var_dump($sql);
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
		$sqlWhere = " where 1 ";
        //dump($this->input);
 		$id = $this->dbh->getOne("select player_id from {$dbh->dbname}.log_info where name = '{$kw['kw_player_name']}'") ;
  		if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']) && ($id!=''))
 		{
 		//dump($id);exit;
 	    //如果能在对应的数据库找到ID那么执行搜索，如果ID为空那么提示名字错误	
 			$sqlWhere .= " and player_id like('%{$id}%') ";
        }elseif(isset($kw['kw_player_name']) && strlen($kw['kw_player_name'])){
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        echo "<script>alert('名字错误');window.location.href='?mod=log&act=invest_plan_reward';</script>";
       //echo $url;
        exit;    
        }       

		if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))   //strlen获得长度值
		{
			$sqlWhere .= " and ctime >= '{$kw['kw_reg_st']}' and ctime <= '{$kw['kw_reg_et']}'";
		}							
		
		$sqlWhere .= isset($kw['kw_player_id']) && strlen($kw['kw_player_id']) ? " and  player_id = '{$kw['kw_player_id']}'" : '';
		$sqlWhere .=" order by id desc";
		return $sqlWhere;
     }

}
