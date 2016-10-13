<?php

class Act_Callback extends ChargeCallback{

    public $platformCfg;
    public $serverCfg;
    public $gameServerId;

    public function __construct(){
        parent::__construct();
        $this->platformId = $this->input['mod'];
        $this->platformCfg = Config::getInstance($this->platformId);
        $psid = $this->input['zoneid'];
        $psid2lgsid = $this->platformCfg->get('psid2lgsid');
        if(isset($psid2lgsid[$psid])){
            $lgsid = $psid2lgsid[$psid];
        }else{
            $lgsid = $psid - 8;
        }
        $lgsid2tgsid = $this->platformCfg->get('lgsid2tgsid');
        if(isset($lgsid2tgsid[$lgsid])){
            $tgsid = $lgsid2tgsid[$lgsid];
        }else{
            $tgsid = $lgsid;
        }
        $this->gameServerId = $lgsid;
        $this->serverId = $lgsid;
        $this->serverCfg = Config::getInstance($this->platformId.'_s'.$tgsid);
    }

    public function process(){
        $this->debug = 1;
        $this->platformId = $this->input['mod'];
        $this->account = $this->input['openid'];
        $this->appId = $this->platformCfg->get('appid');
        $this->appKey = $this->platformCfg->get('appkey');
        $this->log("REQUEST: \n".var_export($_REQUEST, TRUE), true);
        // 设置字段名
        $this->myOrderIdField = 'billno';
        $this->platformOrderIdField = 'billno';
        $this->moneyGame = $this->getMoneyGame();
        $this->moneyField = 'amt';
        $this->signField = 'sig';
        $this->appIdField = 'appid';
        $this->requestFields = array(
            'billno',
            'amt',
            'appid',
            'sig',
        );
        $this->setRequest();
        $this->setSignData();
        $this->signErrorInfo = '{"ret":4,"msg":"Sign Error"}';
        $this->failedInfo = '{"ret":4,"msg":"Unknown Error"}';
        $this->appIdErrorInfo = '{"ret":4,"msg":"AppId Error"}';
        // Success Info
        $this->successInfo = '{"ret":0,"msg":"OK"}';
        $this->repeatOrderInfo = '{"ret":0,"msg":"OK"}';
        // Go ...
        $this->run();
    }

    public function getSignString(){
        $str = parent::getSignString();
        $str = str_replace('-', '%2D', $str);
        $str = urlencode($str);
        // $path = urlencode('/cgi-bin/temp.py');
        if($this->input['mod'] == 'tencent_sandbox'){
            $path = urlencode('/tencent_pay_callback_sandbox.php');
        }else{
            $path = urlencode('/tencent_pay_callback.php');
        }
        return 'GET&' . $path . '&' . $str;
    }

    private function setSignData(){
        $signData = $_GET;
        unset($signData['sig']);
        unset($signData['mod']);
        unset($signData['act']);
        unset($signData['PHPSESSID']);
        ksort($signData);
        reset($signData);
        // echo "<pre>";
        // var_dump($signData);
        // echo "</pre>";
        $this->signData = $signData;
    }

    public function getMySign() 
    {
        $mk = $this->getSignString();
        $my_sign = hash_hmac("sha1", $mk, strtr($this->appKey . '&', '-_', '+/'), true);
        $my_sign = base64_encode($my_sign);
        return $my_sign;
    }

    public function charge(){
        $gmIP = $this->serverCfg->get('gmIP');
        $gmPort = $this->serverCfg->get('gmPort');
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->log("socket ip: $gmIP port: $gmPort \n");
        if ($socket === false) {
            $this->log("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
            return false;
        }
        $result = @socket_connect($socket, $gmIP, $gmPort);
        if($result === false) {
            $this->log("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
            return false;
        }
        $time = time();
        $uid = $this->account;
        $pid = 0;
        $sid = $this->gameServerId;
        $oid = $this->request[$this->myOrderIdField];
        $amt = (int)($this->moneyGame / 10);
        $chargekey = $this->platformCfg->get('chargekey');
        $sign = md5($uid.$amt.$oid.$time.$sid.$pid.$chargekey);
        $in = 'e820c512c1a2f9aefbc98d76757dd9e2';
        $in .= "defaultrecharge|$uid|$pid|$time|$amt|$oid|$sid|$sign";
        // ID*price*num
        // //////////////////////////////
        // // 以下是审核支付时使用的
        // //////////////////////////////
        // $payitem = explode('*', $this->input['payitem']);
        // $shopId = $payitem[0];
        // if($shopId < 100){
        //     $amt = (int)($this->moneyGame / 10);
        //     $sign = md5($uid.$amt.$oid.$time.$sid.$pid.'BCIPMsVMQvt0lFiV');
        //     $in .= "defaultrecharge|$uid|$pid|$time|$amt|$oid|$sid|$sign";
        // } else {
        //     $num = $payitem[2];
        //     $sign = md5($uid.$shopId.$num.$oid.$time.$sid.$pid.'BCIPMsVMQvt0lFiV');
        //     $in .= "defaultrecharge2|$uid|$pid|$time|$shopId|$num|$oid|$sid|$sign";
        // }
        // //////////////////////////////
        // $this->log('charge: '.$in);
        @socket_write($socket, $in, strlen($in));
        while ($out = socket_read($socket, 8192)) {
            if('1' != $out){
                $this->log("charge return error: ".$out);
                return false;
            }
        }
        @socket_close($socket);
        return true;
    }

    private function getMoneyGame() {
        if(isset($this->input['payitem'])){
            $payitem = explode('*', $this->input['payitem']);
            if(count($payitem) == 3){
                return (int)($payitem[1] * $payitem[2]);
            }
        }
        return 0;
    }

}
