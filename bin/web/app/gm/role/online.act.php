<?php
/*-----------------------------------------------------+
 * 在线角色列表
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Online extends Page{

    private
        $dbh,
        $limit = 30,
        $id = 0,
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
        $this->id = isset($this->input['id']) ? $this->input['id'] : 0;
        $this->input = trimArr($this->input);
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
    }

    public function process()
    {
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from {$this->dbh->dbname}.log_in_out";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        foreach($data['list'] as $k => $v){
            $data['list'][$k]['args'] = '{"id":"'.$v['player_id'].'","serverid":"'.$this->serverid.'"}'; 
        }

        $data['page_index'] = Utils::pager(Admin::url('', '', array('id' => $this->id), true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('op', $this->op());
        $this->assign('data', $data);
        $this->assign('formAction', Admin::url('', '', '', true));
        $types = array(
            '0' => '全部',
            '1' => '在线',
            '2' => '离线',
        );
        $this->assign('types', Form::select('kw[type]', $types, $this->input['kw']['type']));
        $this->display();
    }

    /**
     * 取得搜索关键字
     * @return array
     */
    private function getKeyword()
    {
        $kw = array();
        if ($this->input['kw']['player_id'])
        {
            $kw['kw_player_id'] = $this->input['kw']['player_id'];
        }elseif(isset($this->input['id']) && $this->input['id'] > 0){
            $kw['kw_player_id'] = $this->input['id'];
        }

        if ($this->input['kw']['type'])
        {
            $kw['kw_type'] = $this->input['kw']['type'];
        }
        if ($this->input['kw']['ip'])
        {
            $kw['kw_ip'] = $this->input['kw']['ip'];
        }
        if ($this->input['kw']['account'])
        {
            $kw['kw_account'] = $this->input['kw']['account'];
        }
        if ($this->input['kw']['player_name'])
        {
            $kw['kw_player_name'] = $this->input['kw']['player_name'];
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
        $sqlWhere = ' where 1 ';
        if(isset($kw['kw_player_id'])){
            $sqlWhere .= " and player_id = '{$kw['kw_player_id']}'";
        }
        if (isset($kw['kw_player_name']) && strlen($kw['kw_player_name']))
        {
            $sqlWhere .= " and player_name like('%{$kw['kw_player_name']}%')";
        }
        if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))
        {
            $sqlWhere .= " and time >= '{$kw['kw_reg_st']}' and time <= '{$kw['kw_reg_et']}'";
        }
        $sqlWhere .= isset($kw['kw_type']) && $kw['kw_type'] > 0 ? " and  type = '{$kw['kw_type']}'" : '';
        $sqlWhere .= isset($kw['kw_ip']) && $kw['kw_ip'] > 0 ? " and  ip = '{$kw['kw_ip']}'" : '';
        $sqlWhere .= isset($kw['kw_account']) && strlen($kw['kw_account']) ? " and  account = '{$kw['kw_account']}'" : '';
        return $sqlWhere;
    }

    /**
     * 生成批量处理按钮
     */
    private function op()
    {
        $btns = array(
            // 'send_email' => array(
            //     '发送邮件',
            //     '?mod=role&act=send_email',
            //     // '确定发送邮件？',
            // ),
        );
        return Form::batchSelector($btns);
    }


}
