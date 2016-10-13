<?php
/**
 * 元宝日志
 */
class Act_Kxzzz extends Page {

    private 
        $limit = 200, 
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
        if (isset($this->input['page']) && is_numeric($this->input['page'])) {
            $this->page = $this->input['page'];
        }
        if(isset($this->input['kw']['event'])){
            $this->curEvent = $this->input['kw']['event'];
        }
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process ()
    {
        $localAccounts = Db::getInstance()->getAllCol1('select * from game_local_account;');
        $localAccountsStr = "'".implode("','", $localAccounts)."'";
        $localPlayerIds = $this->dbh->getAllCol1("select player_id from log_create_role where account in ({$localAccountsStr});");
        $localPlayerIdsStr = implode(",", $localPlayerIds);

        $where = ' where log_type = 87 ';
        $where .= $localPlayerIdsStr != '' ? " and player_id not in ({$localPlayerIdsStr}) " : '';
        if ($this->input['kw']['start_time'] && $this->input['kw']['end_time']) {
            $where .= " and time >=".strtotime($this->input['kw']['start_time']);
            $where .= " and time <=".strtotime($this->input['kw']['end_time']);
        }
        $where .= isset($this->input['kw']['player_id']) && strlen($this->input['kw']['player_id']) 
            ? " and player_id = '{$this->input['kw']['player_id']}'" : '';
        $countYB = $this->dbh->getOne("select sum(yb_diff) from {$this->dbh->dbname}.log_yb" . $where);
        $countYB = $countYB ? $countYB : 0;
        $countPlayer = $this->dbh->getOne("select count(distinct player_id) from {$this->dbh->dbname}.log_yb" . $where);
        $sql = "select * from {$this->dbh->dbname}.log_yb" . $where;
        $totalRecord = $this->dbh->getOne(str_replace("select *", "select count(*)", $sql));
        $maxPager = ceil($totalRecord / $this->limit);
        $maxPager = $maxPager > 0 ? $maxPager - 1 : 0;
        if( $this->page > $maxPager) $this->page = $maxPager;
        $sql .= ' order by id desc';
        $limit = " limit " . ($this->page * $this->limit) . ", {$this->limit}";
        $data = $this->dbh->getAll($sql.$limit);
        $pager = Utils::pager(Admin::url('', '','', true), $totalRecord, $this->page, $this->limit);
        $this->assign('data', $data);
        $this->assign('page', $pager);
        $this->assign('countYB', $countYB);
        $this->assign('countPlayer', $countPlayer);
        $this->display();
    }

}
