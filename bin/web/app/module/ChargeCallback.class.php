<?php
/*-----------------------------------------------------+
 * 充值回调处理
 * charge callback
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/

# android : String = "android";
# ios_91 : String = "ios_91";
# ios_tb : String = "ios_tb";
# android_wdj : String = "android_wdj";
# android_91 : String = "android_91";
# android_uc : String = "android_uc";
# android_xm : String = "android_xm";
#
# CREATE TABLE IF NOT EXISTS `charge_order` (
#   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
#   `isVerified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已通过发货验证',
#   `ctime` int(11) NOT NULL COMMENT '记录时间',
#   `money` int(11) NOT NULL COMMENT '金额（分）',
#   `myOrderId` varchar(100) NOT NULL COMMENT '透传订单ID',
#   `platformOrderId` varchar(100) NOT NULL COMMENT '平台订单ID',
#   `platformName` varchar(32) NOT NULL COMMENT '平台名称',
#   `recvParams` text NOT NULL COMMENT '收到的其它参数',
#   PRIMARY KEY (`id`),
#   UNIQUE KEY `myOrderId` (`myOrderId`)
# ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值订单' AUTO_INCREMENT=1 ;
#
# 2015-11-27
# ALTER TABLE  `log_charge_order` ADD  `chargeNth` INT( 4 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '第几次充值' AFTER  `recvParams` ;

class ChargeCallback extends Page
{

    public $debug = 0;
    public $appId;
    public $appKey;
    public $request;
    public $requestFields;
    public $moneyGame = 0;
    public $moneyField = 'money';
    public $moneyUnit = 1; // 如果单位为元，值置为: 100
    public $platformId;
    public $serverId;
    public $account;
    public $myOrderIdField;
    public $platformOrderIdField;
    public $signSep1 = '=';
    public $signSep2 = '&';
    public $signData;
    public $signField;
    public $appIdField;
    public $appIdErrorInfo = '';
    public $signErrorInfo = '';
    public $successInfo = '';
    public $repeatOrderInfo = '';
    public $failedInfo = '';
    public $dbTableName = 'log_charge_order';

    public function __construct(){
        parent::__construct();
    }

    // return:
    // 0:成功
    // 1:重复订单订
    // 2:发货失败
    public function saveOrder(){
        $data = array();
        $data['ctime'] = time();
        $data['money'] = (int)($this->request[$this->moneyField] * $this->moneyUnit);
        $data['moneyGame'] = $this->moneyGame;
        $data['myOrderId'] = addslashes($this->request[$this->myOrderIdField]);
        $data['platformOrderId'] = $this->request[$this->platformOrderIdField];
        $data['platformId'] = $this->platformId;
        $data['serverId'] = $this->serverId;
        $data['account'] = $this->account;
        $recvParams = $this->request;
        unset($recvParams[$this->appIdField]);
        unset($recvParams[$this->moneyField]);
        unset($recvParams[$this->myOrderIdField]);
        unset($recvParams[$this->platformOrderIdField]);
        unset($recvParams[$this->signField]);
        $recvArray = array();
        foreach ($recvParams as $k => $v) {
            $recvArray[] = $k . '=' . $v;
        }
        $data['recvParams'] = join('&', $recvArray);
        $db = Db::getInstance();
        $isVerified = $db->getOne("select isVerified from {$this->dbTableName} where myOrderId = '{$data['myOrderId']}'");
        if(!$isVerified){
            if(null == $isVerified){
                $chargeNth = $db->getOne("select count(*) from {$this->dbTableName} where account = '{$this->account}' and serverId = '{$this->serverId}' and platformId = '$this->platformId'");
                if($chargeNth == 0){
                    $data['isFirst'] = 1;
                }
                $data['chargeNth'] = $chargeNth + 1;
                $sql = $db->getInsertSql($this->dbTableName, $data);
                $db->exec($sql);
            }
            if($this->charge()){
                $db->exec("update {$this->dbTableName} set `isVerified` = 1 where myOrderId = '{$data['myOrderId']}'");
                return 0;
            }
            // 强制返回成功
            // return 2;
            return 0;
        }else{
            if($this->debug) $this->log('Repeat order:' . $data['myOrderId']);
            return 1;
        }
    }

    public function getSignString(){
        $sign_array = array();
        foreach ($this->signData as $k => $v) {
            $sign_array[] = $k . $this->signSep1 . $v;
        }
        return join($this->signSep2, $sign_array);
    }

    public function getMySign(){
        return md5($this->getSignString());
    }

    public function verifySign(){
        $recvSign = $this->request[$this->signField];
        $mySign = $this->getMySign();
        if($recvSign != $mySign){
            if($this->debug){
                // echo "Error Sign: {$mySign} != {$recvSign}<br>";
                $this->log("Error Sign: {$mySign} != {$recvSign}\n");
            }
            return false;
        }
        return true;
    }

    public function verifyAppId(){
        if($this->appId == '' || $this->appIdField == '') return true;
        if($this->appIdErrorInfo == '') throw new Exception("Undefined appIdErrorInfo!");
        // 验证AppId
        $recvAppId = $this->request[$this->appIdField];
        if($recvAppId != $this->appId){
            if($this->debug){
                $this->log("Error AppId:{$this->appId} != {$recvAppId}\n");
            }
            return false;
        }
        return true;
    }

    public function setRequest(){
        $params = array();
        foreach($this->requestFields as $v){
           if(isset($_REQUEST[$v])) $params[$v] = $_REQUEST[$v];
        }
        $this->request = $params;
    }

    public function setRequest2($data_key, $sign_key){
        $content = stripslashes($_REQUEST[$data_key]);
        $params = array();
        $params = json_decode($content,true);
        $params['sign'] = $_REQUEST[$sign_key];
        $this->request = $params;
    }

    public function run(){
        try {
            if($this->debug) $this->log(var_export($_REQUEST, TRUE), true);
            if($this->signErrorInfo == '') throw new Exception("Undefined signErrorInfo!");
            if($this->failedInfo == '') throw new Exception("Undefined failedInfo!");
            if($this->successInfo == '') throw new Exception("Undefined successInfo!");
            // 验证AppId
            if(!$this->verifyAppId()){
                echo $this->appIdErrorInfo;
                if($this->debug) $this->log('[ERROR] Verify AppId Failed!');
                return;
            }
            // 验证签名
            if(!$this->verifySign()){
                echo $this->signErrorInfo;
                if($this->debug) $this->log('[ERROR] Verify Sign Failed!');
                return;
            }
            // Save
            // return:
            // 0:成功
            // 1:重复订单订
            // 2:发货失败
            $result = $this->saveOrder();
            if($result == 1 && $this->repeatOrderInfo != ''){
                // Repeat Order
                echo $this->repeatOrderInfo;
                return;
            }else if($result == 2){
                // Save Failed
                echo $this->failedInfo;
                return;
            }
            // Success
            echo $this->successInfo;
        } catch (Exception $e) {
            // Exception
            $this->log('[ERROR] '.$e->getMessage());
            echo $this->failedInfo;
        }
    }

    public function log($content, $writeRequestInfo = false){
        if($writeRequestInfo){
            $requestInformation = $_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'].', http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']."\n";
        }else{
            $requestInformation = '';
        }
        $file = @fopen('./log/callback_'.$this->platformId.'_'.$this->appId.'.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");
        @fclose($file);
    }

    public function charge()
    {
        $this->log('NOT define charge function!');
    }
}
