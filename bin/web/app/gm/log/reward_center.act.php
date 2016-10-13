<?php
class Act_Reward_center extends Page{   
	private

	$limit = 30,
	$page = 0,
	$orderField;
	
	public
	$platformid,
	$dbh,//为了翻译角色名称，需要定义为public属性
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
		if(is_numeric($this->input['kw']['order_field'])) $this->orderField = $this->input['kw']['order_field'];
		
		$orderFields = include(WEB_DIR.'/gm/assets/js/reward_center.php');
		$orderFields['0']="全部类型";
		ksort($orderFields);//根据键，以升序对关联数组进行排序
		//dump($this->orderField);
		//dump($orderFields);exit;
		//生成下拉框表单
		$this->assign('orderFields', Form::select('kw[order_field]', $orderFields, $this->orderField));
	}

	public function process(){
		//dump($this->input);
		$data = array();
		$kw = $this->getKeyword();    //获得时间戳
		$sqlWhere = $this->getSqlWhere($kw);   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.log_reward_center";
		$totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
		$data['list'] = $this->getList($sql . $sqlWhere);
		//echo $sql.$sqlWhere;
		$data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
		$this->assign('kw', $kw);
		$this->assign('data', $data);
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
		if (($this->input['kw']['player_id']))
		{
			$kw['kw_player_id'] = $this->input['kw']['player_id'];
		}
		if (($this->input['kw']['player_name']))
        {
			$kw['kw_player_name'] = $this->input['kw']['player_name'];
		}
		if (($this->input['kw']['type']))
		{
			$kw['kw_type'] = $this->input['kw']['type'];
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
		$sqlWhere = " where 1 ";
 		$id = $this->dbh->getOne("select player_id from {$dbh->dbname}.log_info where name = '{$kw['kw_player_name']}'") ;
	
		if (is_numeric($this->orderField)&&(($this->orderField)!='0'))
		{

			$sqlWhere .= " and type= {$this->orderField}";
		}
		if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))   //strlen获得长度值
		{
			$sqlWhere .= " and ctime >= '{$kw['kw_reg_st']}' and ctime <= '{$kw['kw_reg_et']}'";
		}							//create_time字段保存创建角色时间
  		if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']) && ($id!=''))
 		{

 			//dump($id);exit;
 			
 			$sqlWhere .= " and player_id like('%{$id}%') ";
        }elseif(isset($kw['kw_player_name']) && strlen($kw['kw_player_name'])){
        	$url = Admin::url ();
            
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        echo "<script>alert('名字错误');window.location.href='?mod=log&act=reward_center';</script>";
       //echo $url;
        exit;    
        }       

		$sqlWhere .= isset($kw['kw_player_id']) && strlen($kw['kw_player_id']) ? " and  player_id = '{$kw['kw_player_id']}'" : '';
		$sqlWhere .=" order by id desc";
		return $sqlWhere;
	
	}

}
