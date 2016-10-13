<?php

class Act_Charge extends DefaultCharge{

    public function __construct(){
        parent::__construct();
        $this->paramKeys = array(
            'account',
            'act',
            'billno',
            'money',
            'platform',
            'serverid',
            'time',
            'yb',
            'sign',

        );
        $this->debug = true;
        $this->init();
        $this->appKey = $this->platformCfg->get('chargekey');
    }

    public function process(){
        $dbh = GameDb::getGameDbInstance($this->platformId, $this->targetGameSid);
        $check = $dbh->getRow("select count(*) from {$dbh->dbname}.log_info where sid = {$this->logicGameSid} and account = '{$this->input['account']}'");
        if(!$check) exit('{"ret":6,"msg":"Account Error"}');
        $this->account = $this->input['account'];
        $this->log("REQUEST: \n".var_export($_REQUEST, TRUE), true);
        // 设置字段名
        $this->myOrderIdField = 'billno';
        if($this->input['platform'] == 'kuaiwan'){
            $this->moneyUnit = 100;
        }
        $this->platformOrderIdField = 'billno';
        $this->moneyGame = $this->input['yb'];
        $this->moneyField = 'money';
        $this->signField = 'sign';
        $this->requestFields = array(
            'billno',
            'money',
            'sign',
            'yb',
        );
        $this->setRequest();
        $this->setSignData();
        $this->signErrorInfo = '{"ret":2,"msg":"Sign Error"}';
        $this->failedInfo = '{"ret":99,"msg":"Unknown Error"}';
        $this->appIdErrorInfo = '{"ret":99,"msg":"AppId Error"}';
        // Success Info
        $this->successInfo = '{"ret":0,"msg":"OK"}';
        $this->repeatOrderInfo = '{"ret":0,"msg":"OK"}';
        // Go ...
        $this->run();
    }

    public function charge(){
        $gmIP = $this->serverCfg->get('gmIP');
        $gmPort = $this->serverCfg->get('gmPort');
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->log("socket ip: $gmIP port: $gmPort \n");
        if ($socket === false) {
            $this->log("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
            return false;
        }
        $result = socket_connect($socket, $gmIP, $gmPort);
        if($result === false) {
            $this->log("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
            return false;
        }
        $time = time();
        $uid = $this->account;
        $pid = 0;
        $sid = $this->logicGameSid;
        $oid = $this->request[$this->myOrderIdField];
        $amt = (int)($this->moneyGame / 10);
        $sign = md5($uid.$amt.$oid.$time.$sid.$pid.$this->appKey);
        $in = 'e820c512c1a2f9aefbc98d76757dd9e2';
        $in .= "defaultrecharge|$uid|$pid|$time|$amt|$oid|$sid|$sign";
        socket_write($socket, $in, strlen($in));
        while ($out = socket_read($socket, 8192)) {
            if('1' != $out){
                $this->log("charge return error: ".$out);
                //dump('OK:'.$in);
                return false;
                //dump('E:'.$in);
            }
        }
        socket_close($socket);
        return true;
    }

}
