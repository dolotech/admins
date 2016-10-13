<?php

class Act_Query2 extends DefaultAPI{

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
        exit;
        $gmact = "queryPlayer|{$this->input['account']}|{$this->input['serverid']}";
        $result = GmAction::send($gmact, $this->input['serverid'], $this->platformId);
        if($result['ret'] == 0){
            echo $result['msg'];
        }else{
            echo '{"ret":9,"msg":"Error"}';
        }
    }

}
