<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/


class DefaultAPI extends Page
{

    public $platformCfg;
    public $serverCfg;
    public $platformId;
    public $platformSid;
    public $logicGameSid;
    public $targetGameSid;
    public $signField = 'sign';
    public $signSep1 = '=';
    public $signSep2 = '&';
    public $appKey;
    public $debug = false;
    public $paramKeys = array();

    public function __construct(){
        parent::__construct();

    }

    public function init(){
        $chk = Utils::check_keys($this->paramKeys, $this->input);
        if($chk !== true){
            exit('{"ret":1,"msg":"Missing '.$chk.'"}');
        }
        $this->platformSid = $this->input['serverid'];
        $this->platformId = $this->input['platform'];
        if(!file_exists(CFG_DIR.'/'.$this->platformId.'.cfg.php')){
            exit ('{"ret":4,"msg":"Platform Error"}');
        }
        $this->platformCfg = Config::getInstance($this->platformId);
        $psid2lgsid = $this->platformCfg->get('psid2lgsid');
        if(isset($psid2lgsid[$this->platformSid])){
            $lgsid = $psid2lgsid[$this->platformSid];
        }else{
            $lgsid = $this->getDefaultGameSid();
        }
        $lgsid2tgsid = $this->platformCfg->get('lgsid2tgsid');
        if(isset($lgsid2tgsid[$lgsid])){
            $tgsid = $lgsid2tgsid[$lgsid];
        }else{
            $tgsid = $lgsid;
        }
        $this->logicGameSid = $lgsid;
        $this->targetGameSid = $tgsid;
        $svCfgFile = CFG_DIR.'/'.$this->platformId.'_s'.$this->targetGameSid.'.cfg.php';
        if(!file_exists($svCfgFile)){
            exit ('{"ret":5,"msg":"Server Error"}');

        }
        $this->serverCfg = Config::getInstance($this->platformId.'_s'.$this->targetGameSid);
    }

    public function getDefaultGameSid() {
        if('tencent' == $this->platformId){
            return Game::tencentSidToGameSid($this->platformSid);
        }
        return $this->platformSid;
    }

    public function getSignString(){
        if(!$this->signData) $this->setSignData();
        $sign_array = array();
        foreach ($this->signData as $k => $v) {
            $sign_array[] = $k . $this->signSep1 . $v;
        }
        return join($this->signSep2, $sign_array).'&'.$this->appKey;
    }

    public function getMySign(){

        // dump($this->getSignString());
        return md5($this->getSignString());
    }

    public function verifySign(){
        if((TIMESTAMP - $this->input['time']) > (3600 * 24)){
            exit('{"ret":3,"msg":"Sign Timeout"}');
        }
        $recvSign = $_REQUEST[$this->signField];
        $mySign = $this->getMySign();
        if($recvSign != $mySign){
            if($this->debug){
                // echo "Error Sign: {$mySign} != {$recvSign}\n";
                $this->log("Error Sign: {$mySign} != {$recvSign}\n");
            }
            exit('{"ret":2,"msg":"Sign Error"}');
        }
    }

    public function setSignData(){
        $signData = $_GET;
        unset($signData[$this->signField]);
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
        $file = @fopen('/data/log/'.$this->platformId.date('_Ym').'.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        @fclose($file); 
    }

}
