<?php

/* -----------------------------------------------------+
 * 聊天查询
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Chat extends Page
{

    private
        $dbh,
        $limit = 30,
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
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        $this->input = trimArr($this->input);
        $this->dbh = Db::getInstance();
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }
    }

    public function process()
    {
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from log_chat";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
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
        if (($this->input['kw']['player_id']))
        {
            $kw['kw_player_id'] = $this->input['kw']['player_id'];
        }
        if (($this->input['kw']['player_name']))
        {
            $kw['kw_player_name'] = $this->input['kw']['player_name'];
        }
        if (($this->input['kw']['msg']))
        {
            $kw['kw_msg'] = $this->input['kw']['msg'];
        }
        if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
        {
            $kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);
            $kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);
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
            if($row['server_id'] > 0){
                if(!$row['platform']) $row['platform'] = 'tencent';
                $row['args'] = '{"id":"'.$row['player_id'].'","platformid":"'.$row['platform'].'","serverid":"'.$row['server_id'].'"}'; 
                $row['level'] = Game::getLevel($row['player_id'], $row['server_id'], $row['platform']);
                // $row['isOnline'] = Game::isOnline($row['player_id'], $this->dbh);
            }else{
                $row['args'] = '{}'; 
                $row['level'] = '-';
                // $row['isOnline'] = Game::isOnline($row['player_id'], $this->dbh);
            }
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
        if($this->platformid == 'tencent'){
            $sqlWhere = " where 1";
        }else{
            $sqlWhere = " where platform = '{$this->platformid}'";
        }
        if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']))
        {
            $sqlWhere .= " and player_name like('%{$kw['kw_player_name']}%')";
        }
        if (isset($kw['kw_msg']) && strlen($kw['kw_msg']))
        {
            $sqlWhere .= " and msg like('%{$kw['kw_msg']}%')";
        }
        if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))
        {
            $sqlWhere .= " and time >= '{$kw['kw_reg_st']}' and time <= '{$kw['kw_reg_et']}'";
        }
        $sqlWhere .= isset($kw['kw_account']) && strlen($kw['kw_account']) ? " and  account = '{$kw['kw_account']}'" : '';
        $sqlWhere .= isset($kw['kw_player_id']) && strlen($kw['kw_player_id']) ? " and  player_id = '{$kw['kw_player_id']}'" : '';
        return $sqlWhere;
    }

}
