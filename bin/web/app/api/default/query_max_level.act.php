<?php

class Act_Query_max_level extends DefaultAPI{

    public function __construct(){
        parent::__construct();
        $this->paramKeys = array(
            'account',
            'act',
            'platform',
            'start_serverid',
            'time',
            'sign',
        );
        $chk = Utils::check_keys($this->paramKeys, $this->input);
        if($chk !== true){
            exit('{"ret":1,"msg":"Missing '.$chk.'"}');
        }
        $this->platformId = $this->input['platform'];
        if(!file_exists(CFG_DIR.'/'.$this->platformId.'.cfg.php')){
            exit ('{"ret":4,"msg":"Platform Error"}');
        }
        $this->platformCfg = Config::getInstance($this->platformId);
        $this->appKey = $this->platformCfg->get('loginkey');
    }

    public function process(){
        $this->verifySign();
        $ps = Config::getInstance()->get('platformsServers');
        $rt = array();
        $gmact = "queryMaxLevel|{$this->input['account']}";
        $maxLevel = 0;
        $serverid = 0;
        foreach($ps as $pid => $servers){
            if($pid != $this->platformId) continue;
            $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
            $lgsid2tgsid = Config::getInstance($pid)->get('lgsid2tgsid');
            foreach($servers as $sid){
                if(isset($lgsid2tgsid[$sid])){
                    continue;
                }
                if($sid < $this->input['start_serverid'] || $sid > 1999) continue;
                $result = GmAction::send($gmact, $sid, $this->platformId);
                if($result['ret'] == 0 && $result['msg'] && $result['msg'] > $maxLevel){
                    $serverid = $sid;
                    $maxLevel = $result['msg'];
                }
            }
        }
        if($maxLevel){
            $gmact = "queryName|{$this->input['account']}|{$serverid}";
            $result = GmAction::send($gmact, $serverid, $this->platformId);
            $name = '';
            if($result['ret'] == 0){
                $name = $result['msg'];
            }
            echo '{"ret":0,"msg":"OK","data":{"serverid":'.$serverid.',"name":"'.$name.'","level":'.$maxLevel.'}}';
        }else{
            echo '{"ret":6,"msg":"Account Error"}';
        }
    }

}
