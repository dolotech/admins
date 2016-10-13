<?php

/* -----------------------------------------------------+
 * 后台帐号列表
 * @author yeahoo2000@gmail.com
 * @author Tim <mianyangone@gmail.com>
  +----------------------------------------------------- */

class Act_List extends Page
{
    private
        $dbh,
        $limit = 30,
        $orderField = 'level',
        $orderType = 'desc',
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
            $this->page = $this->input['page'];
        }
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        if($this->input['kw']['order_field']) $this->orderField = $this->input['kw']['order_field'];
        if($this->input['kw']['order_type']) $this->orderType = $this->input['kw']['order_type'];
        $orderFields = array(
            'level' => '等级',
            'yb' => '元宝',
            'lj' => '礼金',
            'lj' => '礼金',
            'coin' => '银两',
            'rmb' => '累计充值',
            'vip_level' => 'VIP等级',
            'login_time' => '登陆时间',
            'create_time' => '创建时间',
        );
        $orderTypes = array(
            'desc' => '降序',
            'asc' => '升序',
        );
        $this->assign('orderFields', Form::select('kw[order_field]', $orderFields, $this->orderField, false, ' class="am-input-sm" '));
        $this->assign('orderTypes', Form::select('kw[order_type]', $orderTypes, $this->orderType, false, ' class="am-input-sm" '));
    }

    public function process()
    {
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by {$this->orderField} {$this->orderType}";
        $sql = "select * from {$this->dbh->dbname}.log_info ";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $totalPages = $totalRecord / $this->limit;
        // 如果当前页数大于总页数，当前页数置0
        // 注意：$this->page == 0 时为第一页
        $this->page = ($this->page + 1) > $totalPages ? 0 : $this->page;
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        // foreach($data['list'] as $k => $v){
        // }

        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $localAccounts = Db::getInstance()->getAllCol1('select * from game_local_account;');
        $this->assign('localAccounts', $localAccounts);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->assign('formAction', Admin::url('', '', '', true));
        $this->display();
    }

    /**
     * 取得搜索关键字
     * @return array
     */
    private function getKeyword()
    {
        $kw = array();
        if (($this->input['kw']['account']))
        {
            $kw['kw_account'] = $this->input['kw']['account'];
        }
        if (($this->input['kw']['playerids']))
        {
            $kw['kw_playerids'] = $this->input['kw']['playerids'];
        }
        if (($this->input['kw']['player_id']))
        {
            $kw['kw_player_id'] = $this->input['kw']['player_id'];
        }
        if (($this->input['kw']['player_name']))
        {
            $kw['kw_player_name'] = $this->input['kw']['player_name'];
        }
        if (($this->input['kw']['ip']))
        {
        	$kw['kw_ip'] = $this->input['kw']['ip'];
        }
        if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
        {
            $kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);
            $kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);
        }
        if (($this->input['kw']['create_st']) && ($this->input['kw']['create_et']))
        {
            $kw['kw_create_st'] = strtotime($this->input['kw']['create_st']);
            $kw['kw_create_et'] = strtotime($this->input['kw']['create_et']);
        }
        if (($this->input['kw']['login_st']) && ($this->input['kw']['login_et']))
        {
            $kw['kw_login_st'] = strtotime($this->input['kw']['login_st']);
            $kw['kw_login_et'] = strtotime($this->input['kw']['login_et']);
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
        $list = array();
        while ($row = $this->dbh->fetch_array($rs)) {
            $row['args'] = '{"id":"'.$row['player_id'].'","account":"'.$row['account'].'","serverid":"'.$row['sid'].'","platformid":"'.$this->platformid.'"}'; 
            // $row['level'] = Game::getLevel($row['player_id'], $this->dbh);
            $row['isOnline'] = Game::isOnline($row['player_id'], $this->dbh, $this->platformid);
            // $row['status'] = Game::getStatus($row['player_id'], $this->dbh);
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
        if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']))
        {
            $sqlWhere .= " and name like('%{$kw['kw_player_name']}%')";
        }
        if (isset($kw['kw_ip']) && strlen($kw['kw_ip']))
        {
            $sqlWhere .= " and ip like('%{$kw['kw_ip']}%')";
        }
        if (isset($kw['kw_playerids']) && strlen($kw['kw_playerids']))
        {
            $sqlWhere .= " and player_id in({$kw['kw_playerids']})";
        }
        if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))
        {
            $sqlWhere .= " and create_time >= '{$kw['kw_reg_st']}' and create_time <= '{$kw['kw_reg_et']}'";
        }
        if (isset($kw['kw_create_st']) && strlen($kw['kw_create_et']))
        {
            $sqlWhere .= " and create_time >= '{$kw['kw_create_st']}' and create_time <= '{$kw['kw_create_et']}'";
        }
        $sqlWhere .= isset($kw['kw_account']) && strlen($kw['kw_account']) ? " and  account = '{$kw['kw_account']}'" : '';
        $sqlWhere .= isset($kw['kw_player_id']) && strlen($kw['kw_player_id']) ? " and  player_id = '{$kw['kw_player_id']}'" : '';

        return $sqlWhere;
    }

}
