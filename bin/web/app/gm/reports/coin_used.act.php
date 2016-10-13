<?php

/* -----------------------------------------------------+
 * 银两消费统计
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Coin_used extends Page {

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
        $logType = include(APP_DIR.'/log_type.cfg.php');
        $this->assign('logType', $logType);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $kw = $this->input['kw'];
        $where = $this->getSqlWhere($kw);
        $sql1 = "SELECT SUM(coin_diff) as num, log_type ";
        $sql2 = " FROM {$this->dbh->dbname}.log_coin {$where} group by log_type";
        $sql3 = " FROM {$this->dbh->dbname}.log_coin {$where}";
        $sql = $sql1 . $sql2;
        $data = $this->dbh->getAll($sql);
        $all = $this->dbh->getOne('select sum(coin_diff)'.$sql3);
        $this->assign('all', $all);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 构造SQL where字串
     * @param array $kw 搜索关键字
     */
    private function getSqlWhere($kw)
    {
        $localAccounts = Db::getInstance()->getAllCol1('select * from game_local_account;');
        $localAccountsStr = "'".implode("','", $localAccounts)."'";
        $localPlayerIds = $this->dbh->getAllCol1("select player_id from log_create_role where account in ({$localAccountsStr});");
        $localPlayerIdsStr = implode(",", $localPlayerIds);

        $sqlWhere = " where coin_diff < 0";
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
