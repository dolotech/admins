<?php

/* -----------------------------------------------------+
 * 神魔探险消费统计
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Smtx extends Page {

    public 
        $dbh,
        $platformid,
        $serverid;

    public function __construct() {
        parent::__construct();
        $this->setPlatformidServerid(); 
        $this->input = trimArr($this->input);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $kw = $this->input['kw'];
        $where = $this->getSqlWhere($kw);
        $sql1 = "SELECT yb_diff, count(player_id) as times, count(distinct player_id) as players, SUM(yb_diff) as ybnum ";
        $sql2 = " FROM {$this->dbh->dbname}.log_yb {$where} group by yb_diff";
        $sql = $sql1 . $sql2;
        $data = $this->dbh->getAll($sql);
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

        $sqlWhere = " where log_type = 87";
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
