<?php
class Act_Mount extends Page{     
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
		
		if(!isset($this->input['id'])){
			exit("No ID");
		}
		$this->id = $this->input['id'];
		// 设置平台ID和服务器ID：
		// 调用$this->setPlatformidServerid();
		// 会自动设置$platformid属性和$serverid属性
		$this->setPlatformidServerid();
		//如果input['page']存在且为数字，那么把当前页的$page改变为url的页数
		if(isset($this->input['page']) && is_numeric($this->input['page'])){
			$this->page = $this->input['page'];											//分页
		}
		//$this->input获得get和post的值,去除首尾空格
		$this->input = trimArr($this->input);
		//连接腾讯服第N个服数据库
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
		$this->assign('serverid', $this->serverid);
		$this->assign('platformid', $this->platformid);
		
	
	}
		
	private function getKeyword()
	{
		$kw = array();
		// var_dump($this->input)的结果
	
		if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
		{
			$kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);     //获得时间戳
			$kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);     //获得保存时间戳的数组
		}
		return $kw;
	}

	public function process(){
		$kw = $this->getKeyword();
		$where = " where player_id = '{$this->id}'";
		
		$where .= isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']) ?" and time >= '{$kw['kw_reg_st']}' and time <= '{$kw['kw_reg_et']}'":'';
		
		
		
		$sql = "select * from {$this->dbh->dbname}.log_mount ".$where;   //$this->dbh->dbname 读取数据库 名，可以通过更改log_xxx来修改查询的表
		$rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);  //从第x条开始读取$this->limit=30条
		$data = array();					//当前页*每页显示的条数                                        从数据库里读取每页显示的条数
		while ($row = $this->dbh->fetch_array($rs)) {    //把数据保存到数组里
			$data[] = $row;       //把每条信息放进数组里
		}
		//echo $sql;
		$totalRecord = $this->dbh->getOne("SELECT COUNT(*) FROM {$this->dbh->dbname}.log_mount".$where);        //分页
		$pages = Utils::pager(Admin::url('', '', array('id' => $this->id), true), $totalRecord, $this->page,$this->limit);	 //分页
		$this->assign('pages', $pages);							//总条数              //当前页数                      //设置右下方显示的1/xxx页
		$this->assign('data', $data);
		$this->assign('kw', $kw);      //为了搜索后保留搜索关键字在搜索框里		

		
		$this->display();
		
	}

}