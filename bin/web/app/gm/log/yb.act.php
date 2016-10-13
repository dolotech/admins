<?php
/**
 * 元宝日志
 */
class Act_Yb extends Page {

    private 
        $limit = 30, 
        $page = 0;

    public 
        $type_s,
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
        if (isset($this->input['page']) && is_numeric($this->input['page'])) {
            $this->page = $this->input['page'];
        }
        
        if(isset($this->input['kw']['event'])){
            $this->curEvent = $this->input['kw']['event'];
        }
        if(is_numeric($this->input['kw']['type'])) $this->type_s = $this->input['kw']['type'];
        $type_arr=array(
        		'0'=>'全部类型',
        		'1'=>'元宝消耗',
        		'2'=>'礼金消耗'
        
        );
        $this->assign('type_select', Form::select('kw[type]', $type_arr, $this->type_s));
        $logType = include APP_DIR.'/log_type.cfg.php';
        //dump($_SERVER['PHP_SELF']);
        $this->assign('logType', $logType);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $kw = $this->input['kw'];
        $where = $this->getSqlWhere($kw);
        $sql = "select * from {$this->dbh->dbname}.log_yb" . $where;
        $totalRecord = $this->dbh->getOne(str_replace("select *", "select count(*)", $sql));//总条数
        //$maxPager = ceil($totalRecord / $this->limit);//总页数
       // $maxPager = $maxPager > 0 ? $maxPager - 1 : 0;//实际的总页数比上面计算的小一
        //if( $this->page > $maxPager) $this->page = $maxPager;//原本是post提交的，后来改成get就无此bug。BUG:POST提交时使用，翻到第N页，然后进行搜索，如果搜索结果少于N页那么就会出BUG，加上这行代码就OK
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
    	if (($this->input['kw']['type']))
    	{
    		$kw['kw_type'] = $this->input['kw']['type'];
    	}

        //echo $kw['kw_type'];
    	$logTypeStr=trim($this->input['kw']['submit_type'], ',');//去掉首尾逗号
    	$type = explode(',',$logTypeStr);//连接成数组
    	$this->assign('type',$type);    	
    	//dump($type);
       //dump($log_type);
        $sqlWhere = " where 1";
        if(isset($logTypeStr) && $logTypeStr != ''){
        	
           // $logTypeStr = implode(',', $kw['log_type']);
            $sqlWhere .= " and log_type in ($logTypeStr)";
        }
        
        if (isset($kw['kw_type']) && strlen($kw['kw_type'])&&($kw['kw_type']=='1'))
        {
        	$sqlWhere .= " and yb_diff != 0 ";
        }
        
        if (isset($kw['kw_type']) && strlen($kw['kw_type'])&&($kw['kw_type']=='2'))
        {
        	$sqlWhere .= " and bind_yb_diff != 0 ";
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
