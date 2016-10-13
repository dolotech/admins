<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/


class TencentProxy extends Action
{

    public $dstHost = '221.228.207.16';
    public $platformCfg;
    public $serverCfg;
    public $platformId = 'tencent';
    public $protocol = 'https';
    public $sdk;
    public $signData;
    public $signSep1 = '=';
    public $signSep2 = '&';

    public function __construct(){
        parent::__construct();
        $this->platformCfg = Config::getInstance('tencent');
    }

    public function init_sdk(){
        // sandbox
        // $apiserver = '119.147.19.43';
        // $this->protocol = 'http';
        // release
        $apiserver = 'openapi.tencentyun.com';
        $this->sdk = new OpenApiV3($this->platformCfg->get('appid'), $this->platformCfg->get('appkey'));
        $this->sdk->setServerName($apiserver);
    }

    public function getSignString(){
        $sign_array = array();
        foreach ($this->signData as $k => $v) {
            $sign_array[] = $k . $this->signSep1 . $v;
        }
        $appKey = $this->platformCfg->get('loginkey');
        return join($this->signSep2, $sign_array).'&'.$appKey;
    }

    public function getMySign(){
        // dump($this->getSignString());
        return md5($this->getSignString());
    }

    public function setSignData($signData){
        if(isset($signData['sign'])) unset($signData['sign']);
        unset($signData['mod']);
        ksort($signData);
        reset($signData);
        // echo "<pre>";
        // var_dump($signData);
        // echo "</pre>";
        $this->signData = $signData;
    }
    public function log($content, $writeRequestInfo = false){
        if($writeRequestInfo){
            $requestInformation = $_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'].', http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']."\n";
        }else{
            $requestInformation = '';
        }
        $file = @fopen('./log/tencent_proxy.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        @fclose($file); 
    }
}
