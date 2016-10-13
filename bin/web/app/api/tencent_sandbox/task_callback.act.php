<?php

class Act_Task_callback extends TencentTaskCallback{

    // task_callback:array (
    //   'appid' => '1102857043',
    //   'billno' => '3C732B7B77A699B5A36A3EDD2AF06D7E_1102857043T320150626151031_1',
    //   'cmd' => 'award',
    //   'contractid' => '1102857043T320150626151031',
    //   'openid' => '3C732B7B77A699B5A36A3EDD2AF06D7E',
    //   'payitem' => '10001',
    //   'pf' => 'qzone',
    //   'providetype' => '2',
    //   'sig' => 'D3/iw4/OMI11kQvIxFdqOtEC7Ko=',
    //   'step' => '1',
    //   'ts' => '1435638423',
    //   'version' => 'V3',
    //   'mod' => 'tencent',
    //   'act' => 'task_callback',
    // )

    public function __construct(){
        parent::__construct();
        if(!isset($this->input['openid'])){
            exit('{"ret":103,"msg":"no openid","zoneid":""}');
        }
        if(!isset($this->input['contractid'])){
            exit('{"ret":103,"msg":"no contractid","zoneid":""}');
        }
        $openid = $this->input['openid'];
        $contractid = $this->input['contractid'];
        $sql = "select max(serverid) from tencent_task where openid = '{$openid}' and contractid = '{$contractid}';";
        $this->platformSid = Db::getInstance()->getOne($sql);
        if(!$this->platformSid){
            exit('{"ret":103,"msg":"no serverid","zoneid":""}');
        }
        $this->setSid();
    }

    public function process(){
        $this->debug = 1;
        $this->platformName = 'tencent_task';
        $this->appId = $this->platformCfg->get('appid');
        $this->appKey = $this->platformCfg->get('appkey');
        $this->log("REQUEST: \n".var_export($_REQUEST, TRUE), true);
        // 设置字段名
        $this->appIdField = 'appid';
        $this->myOrderIdField = 'billno';
        $this->platformOrderIdField = 'billno';
        $this->signField = 'sig';
        $this->requestFields = array(
            'billno',
            'appid',
            'sig',
        );
        $this->setRequest();
        $this->setSignData();
        $this->signErrorInfo = '{"ret":103,"msg":"sign error","zoneid":""}';
        $this->appIdErrorInfo = '{"ret":103,"msg":"appid error","zoneid":""}';
        // 标准返回码如下：
        // 0: 步骤已完成 或 奖励发放成功
        // 1: 用户尚未在应用内创建角色
        // 2：用户尚未完成本步骤
        // 3：该步骤奖励已发放过
        // 100: token已过期
        // 101: token不存在
        // 102: 奖励发放失败
        // 103: 请求参数错误
        $this->failedInfo = '{"ret":2,"msg":"error","zoneid":""}';
        // Success Info
        $this->successInfo = '{"ret":0,"msg":"OK","zoneid":""}';
        // Go ...
        $this->run();
    }

    public function getSignString(){
        $str = parent::getSignString();
        $str = str_replace('-', '%2D', $str);
        $str = str_replace('_', '%5F', $str);
        $str = urlencode($str);
        // $path = urlencode('/cgi-bin/temp.py');
        $path = urlencode('/tencent_task_callback.php');
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
        // echo "mk:".$mk."<br>";
        $my_sign = hash_hmac("sha1", $mk, strtr($this->appKey . '&', '-_', '+/'), true);
        $my_sign = base64_encode($my_sign);
        // echo "Sign:".$my_sign."<br>";
        return $my_sign;
    }

    public function check_reward(){
        $sid = $this->logicGameSid;
        $this->serverCfg = Config::getInstance($this->platformId.'_s'.$sid);
        $gmIP = $this->serverCfg->get('gmIP');
        $gmPort = $this->serverCfg->get('gmPort');
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->log("socket ip: $gmIP port: $gmPort \n");
        if ($socket === false) {
            $this->log("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
            return;
        }
        $result = socket_connect($socket, $gmIP, $gmPort);
        if($result === false) {
            $this->log("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n");
            return;
        }
        $openid = $this->input['openid'];
        $billno = $this->input['billno'];
        $contractid = $this->input['contractid'];
        $cmd = $this->input['cmd'];
        $step = $this->input['step'];
        $itemid = $this->input['payitem'];
        $in = 'e820c512c1a2f9aefbc98d76757dd9e2';
        if($itemid == '') $itemid = '0';
        $in .= "tencent_task|$sid|$openid|$billno|$contractid|$step|$cmd|$itemid";
        $this->log('tencent_task: '.$in);
        $out = "";
        socket_write($socket, $in, strlen($in));
        while ($out = socket_read($socket, 8192)) {
            $this->log('tencent_task Result: ' . $out);
        }
        socket_close($socket);
        if($out == 'ok') return 0;
        return -1;
    }
}
