<?php

class Act_Query extends DefaultAPI{

    public function __construct(){
        parent::__construct();
        $this->paramKeys = array(
            'account',
            'act',
            'platform',
            'serverid',
            'time',
            'sign',
        );
        $this->debug = true;
        $this->init();
        $this->appKey = $this->platformCfg->get('loginkey');
    }

    public function process(){
        $this->verifySign();
        $gmact = "queryPlayer|{$this->input['account']}|{$this->input['serverid']}";
        $result = GmAction::send($gmact, $this->input['serverid'], $this->platformId);
        if($result['ret'] == 0){
            echo $result['msg'];
        }else{
            echo '{"ret":9,"msg":"Error"}';
        }
        // $dbh = GameDb::getGameDbInstance($this->platformId, $this->targetGameSid);
        // $data = $dbh->getRow("select player_id, name, level from {$dbh->dbname}.log_info where sid = {$this->logicGameSid} and account = '{$this->input['account']}'");
        // if(!$data) {
        //     $dbh = GameDb::getGameDbInstance2($this->platformId, $this->input['serverid']);
        //     $data = $dbh->getRow("select player_id, name, level from {$dbh->dbname}.log_info where sid = {$this->logicGameSid} and account = '{$this->input['account']}'");
        //     if(!$data) {
        //         exit('{"ret":6,"msg":"Account Error"}');
        //     }
        // }
        // $json = json_encode($data);
        // echo '{"ret":0,"msg":"OK","data":'.$json.'}';
    }

}
