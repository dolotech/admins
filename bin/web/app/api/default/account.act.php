<?php

class Act_Account extends Action{

    public function __construct(){

        parent::__construct();
        
    }

    public function process(){
        
        define('SWJOY_KEY', '28b5e757bc6c77807585b9fc5b879248');
 
        $typeAllowed = array(
            '1' => 5, //禁言
            '2' => 6, //解除禁言
            '3' => 1, //封号
            '4' => 2, //解封
            '5' => 1  //全网封号
        );
        //用户id
        if(!isset($this->input['uid'])){
            //echo 'uid not provide';
            echo -1;
            return;
        }
        //1:禁言 2:解除禁言 3:封禁 4:解除封禁 5:封号(全游戏)
        if(!isset($this->input['type']) || !is_numeric($this->input['type'])){
            //echo 'type not provide';
            echo -1;
            return;
        }
        //对应的区
        if(!isset($this->input['server_id']) || !is_numeric($this->input['server_id'])){
            //echo 'server_id not provide.';
            echo -1;
            return;
        }
        $uid = $this->input['uid'];
        $platformid = 'swjoy';
        
        $type = $this->input['type'];
        //允许的类型不存在
        if(!isset($typeAllowed[$type])){
            echo -1;
            return;
        }
        $reason = ''; 
   
        $hours = 0;
        $time = intval($this->input['time']);
        $serverId = $this->input['server_id'];
        $sign = $this->input['sign']; 
        $uid2 = $this->input['uid'];

        $secret = md5("{$uid2}{$serverId}{$type}{$time}". SWJOY_KEY);

        if($sign!=$secret){
            echo -2;
            return;
        }
        
        // $svCfgFile = CFG_DIR.'/'.$platformid.'_s'.$serverId.'.cfg.php';

        // if(!file_exists($svCfgFile)){
        //     echo -3;
        //     return;
        // }
        
        $act = $typeAllowed[$type];
        $actTime = 0; // 封号或禁言的时间，0为永久
        $id = $this->getId($uid, $platformid, $serverId);
        if(!$id){
            echo -1;
            exit;
        }
        $gmact = "gmact2|{$uid}|{$act}|{$reason}|{$actTime}";
        $result = GmAction::send($gmact, $serverId, $platformid);
        
        if($result['ret'] == 0 && $result['msg'] == 'ok'){

            // $logType = 110 + $type;
            // switch($type){
            // case 1:
            //     $logContent = "封停了{$uid}\n时间：{$hours}\n理由：{$reason}";
            //     break;
            // case 2:
            //     $logContent = "解封了{$uid}\n理由：{$reason}";
            //     break;
            // case 3:
            //     $logContent = "封了{$uid}的IP\n时间：{$hours}\n理由：{$reason}";
            //     break;
            // case 4:
            //     $logContent = "解封了{$uid}的IP\n理由：{$reason}";
            //     break;
            // case 5:
            //     $logContent = "禁言了{$uid}\n时间：{$hours}\n理由：{$reason}";
            //     break;
            // case 6:
            //     $logContent = "解禁了{$uid}\n理由：{$reason}";
            //     break;
            // default:
            //     $logContent = "";
            //     break;
            // }
            // Admin::log($logType, $logContent);
            // Game::setStatus($uid, $type, $actTime, $this->input['server_id'], $platformid);

            echo 1;
            
            return;
        }
        
        echo -1;
    }

    public function getId($aid, $pid, $sid){
        $dbh = GameDb::getGameDbInstance($pid, $sid);
        $id = $dbh->getOne("select player_id from {$dbh->dbname}.log_info where sid = {$sid} and account = '{$aid}'");
        if(!$id) {
            $dbh = GameDb::getGameDbInstance2($pid, $sid);
            $id = $dbh->getOne("select player_id from {$dbh->dbname}.log_info where sid = {$sid} and account = '{$aid}'");
        }
        return $id;
    }
    
}
