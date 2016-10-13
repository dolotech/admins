<?php

/*-----------------------------------------------------+
 * 游戏统计
 *
 * @author rolong@<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class Act_Game_stat_level extends Page{

    public 
        $dbh,
        $platformid,
        $serverid;

    public function __construct(){
        parent::__construct();
        $this->setPlatformidServerid(); 
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }

    public function process(){
        $levelsSql = 'select count(player_id) as num, level from log_info group by level';
        $levelsData = $this->dbh->getAll($levelsSql);
        $data = array();
        foreach($levelsData as $row) {
            $data[$row['level']] = $row['num'];
        }
        $this->assign('data', $data);
        $this->display();
    }
}
