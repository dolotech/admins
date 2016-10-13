<?php

//   'QUERY_STRING' => 'seqid=12c8c336b6570d9e78ea3e11b0336e95&openid=38C2EA450A0EC75F805C70019DA6CF04&openkey=91B6D39ACF4B26602F52DECE1503186B&platform=website&pf=website&serverid=0&pfkey=9b4fd35095435fcc13814095b720aafd&s=0.6675723397638649&&sName=%E5%BC%80%E5%8F%91%E6%B5%8B%E8%AF%95%E6%9C%8D',

class Act_Login extends TencentAPI{

    public $sdk;

    public function __construct(){
        parent::__construct();
        if(!isset($this->input['serverid'])){
            exit("NO SERVERID");
        }
        $this->platformSid = $this->input['serverid'];
        $this->setSid();
        $this->serverCfg = Config::getInstance($this->platformId.'_s'.$this->targetGameSid);
        $this->sdk = new OpenApiV3($this->platformCfg->get('appid'), $this->platformCfg->get('appkey'));
        $this->sdk->setServerName($this->platformCfg->get('apiserver'));
    }

    public function process(){
        $is_login = $this->is_login();
        if($is_login['ret'] != 0){
            exit('ERROR: '.$is_login['msg']);
        }
        // Logger::i("REQUEST: \n".var_export($_REQUEST, TRUE), true);
        $this->log("REQUEST: \n".var_export($_REQUEST, TRUE), true);
        $userInfo = $this->getUserInfo();
        if($userInfo['ret'] != 0){
            exit('ERROR: '.$userInfo['msg']);
        }
        $payUrl = "/api/?mod={$this->platformId}&act=payment_url&serverid={$this->input['serverid']}&openid={$this->input['openid']}&openkey={$this->input['openkey']}&pf={$this->input['pf']}&pfkey={$this->input['pfkey']}";
        $platformArgs = $userInfo['is_yellow_vip'].'|'.$userInfo['is_yellow_year_vip'].'|'.$userInfo['yellow_vip_level'].'|'.$userInfo['is_yellow_high_vip'].'|0';
        $args = array(
            0  => $this->serverCfg->get('serverIP'),
            1  => $this->serverCfg->get('serverPort'),
            2  => $this->logicGameSid, // 游戏服务器ID
            3  => $this->input['openid'], // 账号,
            4  => 1, // 是否成年
            5  => $payUrl,
            6  => '', // 礼包URL
            7  => TIMESTAMP,
            8  => '', // 收藏地址
            9  => 'tencent', // 平台ID
            10 => $platformArgs,
        );
        $info = base64_encode(implode(',', $args));
        $sign = md5(TIMESTAMP . $this->platformCfg->get('loginkey'));
        $relase['info'] = $info;
        $relase['sign'] = $sign;
        $relase['time'] = TIMESTAMP;
        $query = http_build_query($relase);
        $this->assign("query", $query);
        $this->assign("clientUrl", $this->serverCfg->get('clientUrl'));
        $this->assign("clientResUrl", $this->serverCfg->get('clientResUrl'));
        $this->assign("clientVersion", $this->serverCfg->get('clientVersion'));
        $this->assign("gameServerId", $this->logicGameSid);
        $this->assign("appid", $this->platformCfg->get('appid'));
        $this->addTemplate('play');
        // echo '<pre>';
        // var_dump($args);
        // var_dump($_REQUEST);
        // echo '</pre>';
        $this->display();
        if($this->verifyInvKey()){
            if(isset($this->input['app_custom']) && is_numeric($this->input['app_custom'])){
                $invServerId = $this->input['app_custom'];
            }else{
                $invServerId = $this->logicGameSid;
            }
            // %% @doc invite friends UIDBin=邀请人,NewIDBin=被邀请人
            // handle([<<"">>, UIDBin, NewIDBin], Socket)->
            $message = "gminvitefriends|{$this->input['iopenid']}|{$this->input['openid']}";
            GmAction::send($message, $invServerId, $this->platformId);
        }
        if(isset($this->input['app_contract_id'])){
            $this->saveTencentTask(
                $this->input['openid']
                ,$this->input['app_contract_id']
                ,$this->input['serverid']
            );
        }
    }

    public function saveTencentTask($openid, $contractid, $serverid){
        $sql = "INSERT INTO `tencent_task` (`openid`, `contractid`, `serverid`) VALUES ('{$openid}', '{$contractid}', '{$serverid}');";
        Db::getInstance()->exec($sql);
    }

    public function getUserInfo(){
        $params = array(
            'openid' => $this->input['openid'],
            'openkey' => $this->input['openkey'],
            'pf' => $this->input['pf'],
        );
        $script_name = '/v3/user/get_info';
        return $this->sdk->api($script_name, $params,'post', $this->protocol);
    }

    // http://s12.app1102857043.qqopenapp.com/index.php?seqid=d508f0ec5b281f1b039c8ee797ee617e&serverid=12&platform=qzone&s=0.6626791332382709&qz_width=760&openid=38C2EA450A0EC75F805C70019DA6CF04&openkey=E8F06B1FA87896F6B86EA773567CFF4B&pf=qzone&pfkey=3804107938fb1135ff72db7a56f0102a&qz_ver=8&appcanvas=1&qz_style=35&params=&app_rid=&app_tid=&itime=1437446901&invkey=B9F8BF9CF9178F1A1F51092C8C9C4FCF&iopenid=38C2EA450A0EC75F805C70019DA6CF04&app_custom=&app_isfirst=1
    // REQUEST: 
    // array (
    //   'pgv_pvid' => '1260057231',
    //   'pgv_pvi' => '3924317184',
    //   'PHPSESSID' => 'dlq40meqgd2i75p0104vnunro3',
    //   'seqid' => 'd508f0ec5b281f1b039c8ee797ee617e',
    //   'serverid' => '12',
    //   'platform' => 'qzone',
    //   's' => '0.6626791332382709',
    //   'qz_width' => '760',
    //   'openid' => '38C2EA450A0EC75F805C70019DA6CF04',
    //   'openkey' => 'E8F06B1FA87896F6B86EA773567CFF4B',
    //   'pf' => 'qzone',
    //   'pfkey' => '3804107938fb1135ff72db7a56f0102a',
    //   'qz_ver' => '8',
    //   'appcanvas' => '1',
    //   'qz_style' => '35',
    //   'params' => '',
    //   'app_rid' => '',
    //   'app_tid' => '',
    //   'itime' => '1437446901',
    //   'invkey' => 'B9F8BF9CF9178F1A1F51092C8C9C4FCF',
    //   'iopenid' => '38C2EA450A0EC75F805C70019DA6CF04',
    //   'app_custom' => '',
    //   'app_isfirst' => '1',
    //   'mod' => 'tencent_sandbox',
    //   'act' => 'login',
    // )
    // 验证邀请数据
    //
    // { 
    // "ret":0,
    // "is_lost":0,
    // "is_right":"0",
    // }
    public function verifyInvKey(){
        if(!isset($this->input['iopenid'])){
            return false;
        }
        $params = array(
            'openid' => $this->input['openid'],
            'openkey' => $this->input['openkey'],
            'pf' => $this->input['pf'],
            'itime' => $this->input['itime'],
            'invkey' => $this->input['invkey'],
            'iopenid' => $this->input['iopenid'],
        );
        $script_name = '/v3/spread/verify_invkey';
        $result = $this->sdk->api($script_name, $params,'post', $this->protocol);
        if($result['ret'] != 0){
            return false;
        }
        return $result['is_right'] == '1';
    }

    public function is_login() {
        $params = array(
            'openid' => $this->input['openid'],
            'openkey' => $this->input['openkey'],
            'pf' => $this->input['pf'],
            'pfkey' => $this->input['pfkey'],
            'format' => 'json',
        );
        $script_name = '/v3/user/is_login';
        return $this->sdk->api($script_name, $params, 'post', $this->protocol);
    }
}
