<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/


class TencentTaskCallback extends TencentAPI
{

    public $debug = 0;
    public $appId;
    public $appKey;
    public $request;
    public $requestFields;
    public $moneyUnit = 1; // 如果单位为元，值置为: 100
    public $myOrderIdField;
    public $platformOrderIdField;
    public $packageNameField = 'package_name';
    public $signSep1 = '=';
    public $signSep2 = '&';
    public $signData;
    public $platformName;
    public $signField;
    public $appIdField;
    public $appIdErrorInfo = '';
    public $signErrorInfo = '';
    public $successInfo = '';
    public $repeatOrderInfo = '';
    public $failedInfo = '';

    public function __construct(){
        parent::__construct();
    }

    // return:
    // 0:成功
    // 1:重复订单订
    // 2:单号格式错语
    public function saveOrder(){
        return 1;
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

            // return:
            // 0:成功
            // -1:失败
            $result = $this->check_reward();
            if(!$result){
                // Failed
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
        $file = @fopen('./log/callback_'.$this->platformName.'_'.$this->appId.'.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        @fclose($file); 
    }

    public function check_reward()
    {
        $this->log('NOT define check_reward function!');
        return -1;
    }	
}
