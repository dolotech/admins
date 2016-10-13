<?php
class Act_Item extends Page{

	private

        $limit = 30,
        $page = 0;

    public 
        $db,
    	$dbh,//翻译id需要用公共属性
        $platformid,
        $serverid,
    	$orderField;
    public function __construct(){
        parent::__construct();
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $this->setPlatformidServerid(); 
        $this->input = trimArr($this->input);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->db = Db::getInstance();
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];   //这里把url上的page值赋给$page属性
        }
        
        if(is_numeric($this->input['kw']['order_field'])) $this->orderField = $this->input['kw']['order_field'];
        
        $orderFields = include(APP_DIR.'/log_type.cfg.php');
        $orderFields['0']="全部类型";
        ksort($orderFields);//根据键，以升序对关联数组进行排序
        $this->assign('orderFields', Form::select('kw[order_field]', $orderFields, $this->orderField));
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
    }	

	public function process(){
        $ctrl = array(
            '1' => '获得或购买',
            '2' => '消耗或卖出'
        );
		$data = array();
		$kw = $this->getKeyword();   
		//dump($kw);//返回的结果是一个数组 
		$sqlWhere = $this->getSqlWhere($kw);   //获得where语句
		$sql = "select * from {$this->dbh->dbname}.log_item";
		$totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
		$data['list'] = $this->getList($sql . $sqlWhere);
		$data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
		$this->assign('kw', $kw);//搜索后保留搜索关键字
		$this->assign('data', $data);
        $this->assign('ctrl',$ctrl);
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

		if (($this->input['kw']['item_type_id']))
		{
			$kw['kw_item_type_id'] = $this->input['kw']['item_type_id'];
		}
		if (($this->input['kw']['order_field']))
		{
			$kw['kw_order_field'] = $this->input['kw']['order_field'];
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

 		$goods = $this->db->getOne("select goodsid from log_goods where goods_name = '{$kw['kw_item_type_id']}'") ;

        //dump($goods);
 		$id = $this->dbh->getOne("select player_id from {$dbh->dbname}.log_info where name = '{$kw['kw_player_name']}'") ;
		if (isset($kw['kw_item_type_id']) && strlen($kw['kw_item_type_id'])&&is_numeric($kw['kw_item_type_id']))
		{
			$sqlWhere .= " and item_type_id = '{$kw['kw_item_type_id']}'";
        }elseif(isset($kw['kw_item_type_id']) && strlen($kw['kw_item_type_id'])&&($goods!='')){
            $sqlWhere .= "and item_type_id = '{$goods}'";

        
        }
 		if (isset($kw['kw_order_field']) && strlen($kw['kw_order_field']))
 		{
 			$sqlWhere .= " and log_type = '{$kw['kw_order_field']}'";
 		}
		if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))   //strlen获得长度值
		{
			$sqlWhere .= " and time >= '{$kw['kw_reg_st']}' and time <= '{$kw['kw_reg_et']}'";
		}							//create_time字段保存创建角色时间
  		if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']) && ($id!=''))
 		{

 			//dump($id);exit;
 			
 			$sqlWhere .= " and player_id like('%{$id}%') ";
        }elseif(isset($kw['kw_player_name']) && strlen($kw['kw_player_name'])){
        	$url = Admin::url ();
            
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        echo "<script>alert('名字错误');window.location.href='?mod=log&act=item';</script>";
       //echo $url;
        exit;    
        }       
		
		$sqlWhere .= isset($kw['kw_player_id']) && strlen($kw['kw_player_id']) ? " and  player_id = '{$kw['kw_player_id']}'" : '';
		$sqlWhere .=" order by id desc";
		return $sqlWhere;
     }

}
