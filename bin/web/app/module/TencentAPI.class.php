<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/


class TencentAPI extends Page
{

    public $platformCfg;
    public $serverCfg;
    public $platformId;
    public $platformSid;
    public $logicGameSid;
    public $targetGameSid;
    public $sandbox = false;
    public $protocol = 'https';

    public function __construct(){
        parent::__construct();
        $this->platformId = $this->input['mod'];
        if($this->platformId == 'tencent_sandbox'){
            $this->sandbox = true;
            $this->protocol = 'http';
        }
        $this->platformCfg = Config::getInstance($this->platformId);

    }

    public function setSid(){
        if(!$this->platformSid) exit('Error platformSid: '.$this->platformSid);
        $psid2lgsid = $this->platformCfg->get('psid2lgsid');
        if(isset($psid2lgsid[$this->platformSid])){
            $lgsid = $psid2lgsid[$this->platformSid];
        }else{
            $lgsid = Game::tencentSidToGameSid($this->platformSid);
        }
        $lgsid2tgsid = $this->platformCfg->get('lgsid2tgsid');
        if(isset($lgsid2tgsid[$lgsid])){
            $tgsid = $lgsid2tgsid[$lgsid];
        }else{
            $tgsid = $lgsid;
        }
        $this->logicGameSid = $lgsid;
        $this->targetGameSid = $tgsid;
    }

    public function log($content, $writeRequestInfo = false){
        if($writeRequestInfo){
            $requestInformation = $_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'].', http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']."\n";
        }else{
            $requestInformation = '';
        }
        $file = @fopen('./log/tencent_api_'.$this->platformName.'_'.$this->appId.'.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        @fclose($file); 
    }
}
