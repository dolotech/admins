<?php

class Act_Charge_rank extends Action{

    public function __construct(){

        parent::__construct();
        
    }

    public function process(){
        // if(!isset($_GET['player_id'])) exit('no player_id');
        // if(!($_GET['player_id'] > 0)) exit('player_id error');
        // 结束时间,长度,玩家id,玩家名字,玩家充值,...,自己的充值
        if(!isset($_GET['platform'])) {
            Logger::i('no platform', true);
            exit;
        }
        if(!isset($_GET['player_id'])) {
            Logger::i('no player_id', true);
            exit;
        }
        $playerId = $_GET['platform'];
        $db = Db::getInstance();
        $data = $db->getAll("SELECT server_id, player_id, name, yb FROM charge_rank WHERE platform_id = '{$playerId}' order by yb desc, ctime asc limit 10");
        $len = count($data);
        $list = '';
        foreach($data as $row){
            if($row['server_id'] == 2001) $name = 's0.'.$row['name'];
            else $name = 's'.$row['server_id'].'.'.$row['name'];
            $list .= $row['player_id'].','.$name.','.$row['yb'].',';
        }
        $playerId = isset($_GET['player_id']) && $_GET['player_id'] > 0 ? $_GET['player_id'] : 0;
        $myYB = $db->getOne("SELECT yb FROM charge_rank WHERE player_id = '{$playerId}'");
        if(!$myYB) $myYB = '0';
        $endTime = '1447603199';
        echo $endTime.','.$len.','.$list.$myYB;
    }
}
