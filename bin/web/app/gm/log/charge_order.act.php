<?php

/* -----------------------------------------------------+
 * 充值订单查询
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Charge_order extends Page {

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
        $this->dbh = Db::getInstance();//连接默认数据库game
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }
    }

    public function process()
    {
        if(
            isset($this->input['platformId'])
            && isset($this->input['serverId'])
            && isset($this->input['order'])
            && isset($this->input['account'])
            && isset($this->input['yb'])
        ){
            $this->charge(
                $this->input['platformId'], 
                $this->input['serverId'], 
                $this->input['order'], 
                $this->input['account'], 
                $this->input['yb']
            );
            exit;
        }
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from log_charge_order";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
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
        if (($this->input['account']))
        {
        	$kw['kw_account'] = $this->input['account'];
        }
        if (isset($this->input['kw']['isFirst']))
        {
            $kw['kw_isFirst'] = $this->input['kw']['isFirst'];
        }
        if (isset($this->input['kw']['isVerified']))
        {
            $kw['kw_isVerified'] = $this->input['kw']['isVerified'];
        }
        if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
        {
            $kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);
            $kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);
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
        $sids = ChargeStat::getMergeSids($this->platformid, $this->serverid);
        $sqlWhere = " where platformId = '{$this->platformid}' and serverId in ($sids) ";
        if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))
        {
            $sqlWhere .= " and ctime >= '{$kw['kw_reg_st']}' and ctime <= '{$kw['kw_reg_et']}'";
        }
        $sqlWhere .= isset($kw['kw_account']) && strlen($kw['kw_account']) ? " and  account = '{$kw['kw_account']}'" : '';
        $sqlWhere .= isset($kw['kw_isFirst']) && strlen($kw['kw_isFirst']) ? " and  isFirst = '{$kw['kw_isFirst']}'" : '';
        $sqlWhere .= isset($kw['kw_isVerified']) && strlen($kw['kw_isVerified']) ? " and  isVerified = '{$kw['kw_isVerified']}'" : '';
        return $sqlWhere;
    }

    public function charge($platformId, $serverId, $order, $account, $yb){
        $appKey = Config::getInstance($platformId)->get('chargekey');
        $serverCfg = Config::getInstance($platformId.'_s'.$serverId);
        $gmIP = $serverCfg->get('gmIP');
        $gmPort = $serverCfg->get('gmPort');
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        $result = @socket_connect($socket, $gmIP, $gmPort);
        if($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
            exit;
        }
        $time = time();
        $uid = $account;
        $pid = 0;
        $sid = $serverId;
        $oid = $order;
        $amt = (int)($yb / 10);
        $sign = md5($uid.$amt.$oid.$time.$sid.$pid.$appKey);
        $in = 'e820c512c1a2f9aefbc98d76757dd9e2';
        $in .= "defaultrecharge|$uid|$pid|$time|$amt|$oid|$sid|$sign";
        @socket_write($socket, $in, strlen($in));
        while ($out = socket_read($socket, 8192)) {
            if('1' != $out){
                echo "charge return error: ".$out;
                return false;
            }
        }
        @socket_close($socket);
        $this->dbh->exec("update log_charge_order set `isVerified` = 1 where myOrderId = '{$order}'");
        echo 'OK';
    }

}
