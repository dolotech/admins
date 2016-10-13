<?php
class Act_Sevenday_rank extends Page{     

    private
        $dbh,
        $limit = 30,
        $orderField = '2',
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
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        if($this->input['kw']['order_field']) $this->orderField = $this->input['kw']['order_field'];
        $orderFields = array(
            '2' => '坐骑活动',
            '3' => '光翼活动',
            '4' => '海钥活动',
            '5' => '海麟活动',
        );
        //生成下拉框表单
        $this->assign('orderFields', Form::select('kw[order_field]', $orderFields, $this->orderField));
    }

    public function process(){
        $data = array();

        $sqlWhere = $this->getSqlWhere();   //获得where语句
        $sqlOrder = " order by `rank` asc";
        $sql = "select * from {$this->dbh->dbname}.log_sevenday_rank";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 获取列表数据
     * @param string $sql SQL查询字串
     * @return array
     */
    private function getList($sql)
    {													//从第N条开始，读取X条信息
        $rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);
        $this->addParamCache(array('page'=>$this->page));//目前用不到
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
        $sqlWhere = " where type= {$this->orderField} ";
        return $sqlWhere; 
    }

}
