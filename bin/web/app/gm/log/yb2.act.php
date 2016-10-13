<?php
/**
 * 元宝日志
 */
class Act_Yb2 extends Page {

    private 
        $limit = 30, 
        $page = 0;

    public 
        $dbh,
        $platformid,
        $serverid,
		$log_type;
    public function __construct() {
        parent::__construct();
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $this->setPlatformidServerid(); 
        $this->input = trimArr($this->input);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        if (isset($this->input['page']) && is_numeric($this->input['page'])) {
            $this->page = $this->input['page'];
        }
        if(isset($this->input['kw']['event'])){
            $this->curEvent = $this->input['kw']['event'];
        }
        if(is_numeric($this->input['kw']['log_type'])) $this->log_type = $this->input['kw']['log_type'];
        $logType = include APP_DIR.'/log_type.cfg.php';
        $logType['0']="全部类型";
        ksort($logType);//根据键，以升序对关联数组进行排序
        $this->assign('log_type', Form::select('kw[log_type]', $logType, $this->log_type));
        $this->assign('logType', $logType);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $kw = $this->input['kw'];
        //dump($kw);
        $where = $this->getSqlWhere($kw);
        $sql = "select * from {$this->dbh->dbname}.log_yb" . $where;
        $totalRecord = $this->dbh->getOne(str_replace("select *", "select count(*)", $sql));
        $maxPager = ceil($totalRecord / $this->limit);
        $maxPager = $maxPager > 0 ? $maxPager - 1 : 0;
        if( $this->page > $maxPager) $this->page = $maxPager;
        $sql .= ' order by id desc';
        $limit = " limit " . ($this->page * $this->limit) . ", {$this->limit}";
        $data = $this->dbh->getAll($sql.$limit);
        $pager = Utils::pager(Admin::url('', '','', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->assign('page', $pager);
        $this->display();
    }

    /**
     * 构造SQL where字串
     * @param array $kw 搜索关键字
     */
    private function getSqlWhere($kw)
    {
        //dump($this->input);
        $sqlWhere = " where 1 ";
        if(isset($kw['log_type']) && $kw['log_type'] != '0'){
          
            $sqlWhere .= " and log_type = {$kw['log_type']}";
        }
        if (isset($kw['time_start']) && $kw['time_start'] != '' 
            && isset($kw['time_end']) && $kw['time_end'] != '')
        {
            $start = strtotime($kw['time_start']);
            $end = strtotime($kw['time_end']);
            $sqlWhere .= " and time >= '{$start}' and time <= '{$end}'";
        }
        $sqlWhere .= isset($kw['player_id']) && strlen($kw['player_id']) 
            ? " and  player_id = '{$kw['player_id']}'" : '';
        return $sqlWhere;
    }

}
