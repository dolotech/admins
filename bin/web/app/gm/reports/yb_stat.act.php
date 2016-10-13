<?php

/* -----------------------------------------------------+
 * 银两消费统计
 *
 * @author CRX
 +----------------------------------------------------- */

class Act_Yb_stat extends Page {
	private
	$limit = 30,
	$page = 0;
	
	
    public 
        $dbh,
        $platformid,
        $serverid;

    public function __construct() {
        parent::__construct();
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $this->setPlatformidServerid(); 
        $this->input = trimArr($this->input);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        if(isset($this->input['kw']['event'])){
            $this->curEvent = $this->input['kw']['event'];
        }
        
        if (isset($this->input['page']) && is_numeric($this->input['page'])) {
        	$this->page = $this->input['page'];
        }
        
        
        $logType = include(APP_DIR.'/log_type.cfg.php');
        //dump($logType);
        $this->assign('logType', $logType);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $kw = $this->input['kw'];
        $where = $this->getSqlWhere($kw);

        $sql_all = "select * from {$this->dbh->dbname}.log_yb {$where} order by id desc ";
        $sql_sum = "select sum(yb_diff) FROM {$this->dbh->dbname}.log_yb {$where} ";
        $data['list'] = $this->getList($sql_all);
        
        //用于统计图
        $sql1 = "SELECT SUM(yb_diff) as sum, log_type ";
        $sql2 = " FROM {$this->dbh->dbname}.log_yb {$where} group by log_type";
        $data2 = $this->dbh->getAll($sql1.$sql2);
        //dump($data2);
        //显示
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql_all));
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);

        //消耗元宝总数
        $yb_sum = $this->dbh->getOne($sql_sum);
        $yb_sum = abs($yb_sum);

        //echo $yb_sum;
        $this->assign('yb_sum', $yb_sum);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->assign('data2', $data2);
        $this->display();
    }

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
        $sqlWhere = " where yb_diff <0 ";
        if (isset($kw['time_start']) && $kw['time_start'] != '' 
            && isset($kw['time_end']) && $kw['time_end'] != '')
        {
            $start = strtotime($kw['time_start']);
            $end = strtotime($kw['time_end']);
            $sqlWhere .= " and time >= '{$start}' and time <= '{$end}'";
        }
        $sqlWhere .= isset($kw['player_id']) && strlen($kw['player_id']) 
            ? " and player_id = '{$kw['player_id']}'" : '';
        $sqlWhere .= $localPlayerIdsStr != '' ? " and player_id not in ({$localPlayerIdsStr})" : '';
        return $sqlWhere;
    }

}
