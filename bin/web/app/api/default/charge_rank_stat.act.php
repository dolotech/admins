<?php

class Act_Charge_rank_stat extends Action{

    public function __construct(){

        parent::__construct();
        
    }

    public function process(){

        // SIDL++PlayerIDL++NameL++YBL++Key
        $key = "7951b384a1c876756bd042b8d4455b24";
        $platformId = $this->input['platform'];
        $sid = $this->input['sid'];
        $playerId = $this->input['player_id'];
        $name = $this->input['name'];
        $yb = $this->input['yb'];
        $sign = $this->input['sign'];
        $mySign = md5($sid.$playerId.$name.$yb.$key.$platformId);
        if(!$sign || $sign != $mySign){
            Logger::i(var_export($_REQUEST, TRUE));
            Logger::i("mySign:".$mySign);
            exit('error');
        }
        $db = Db::getInstance();
        $myYB = $db->getOne("SELECT yb FROM charge_rank WHERE platform_id = '{$platformId}' and player_id = '{$playerId}'");
        $time = time();
        if($myYB){
            $myYB += $yb;
            $sql = "update charge_rank set yb = '{$myYB}', ctime = '{$time}' where platform_id = '{$platformId}' and player_id = '{$playerId}';";
            $db->exec($sql);
        }else{
            $sql = "INSERT INTO `charge_rank` (`platform_id`, `server_id`, `player_id`, `yb`, `ctime`, `name`) VALUES ('{$platformId}', '{$sid}', '{$playerId}', '{$yb}', '{$time}', '{$name}');";
            $db->exec($sql);
        }
        echo 'ok';
    }
}
