<?php

/* -----------------------------------------------------+
 * 直接登录玩家账号
 +----------------------------------------------------- */

class Act_Login extends DefaultAPI
{
    public function __construct(){
        parent::__construct();
        $this->input['platform'] = $this->input['platformid'];
        $this->paramKeys = array(
            'account',
            'platform',
            'serverid',

        );
        $this->debug = true;
        if($this->input['platform'] == 'tencent') {
            $this->input['serverid'] = Game::gameSidToTencentSid($this->input['serverid']);
        }
        $this->init();
        $this->appKey = $this->platformCfg->get('loginkey');
    }

    public function process(){
        $platformArgs = '';
        $args = array(
            0  => $this->serverCfg->get('serverIP'),
            1  => $this->serverCfg->get('serverPort'),
            2  => $this->logicGameSid, // 游戏服务器ID
            3  => $this->input['account'], // 账号,
            4  => 1, // 是否成年
            5  => $this->platformCfg->get('paymenturl'),
            6  => '', // 礼包URL
            7  => TIMESTAMP,
            8  => '', // 收藏地址
            9  => $this->platformId, // 平台ID
            10 => $platformArgs,
        );
        $info = base64_encode(implode(',', $args));
        $sign = md5(TIMESTAMP . $this->platformCfg->get('loginkey'));
        $relase['info'] = $info;
        $relase['sign'] = $sign;
        $relase['time'] = TIMESTAMP;
        $query = http_build_query($relase);
        $this->assign("query", $query);
        $this->assign("appName", $this->platformCfg->get('appName'));
        $this->assign("appUrl", $this->platformCfg->get('appUrl'));
        $this->assign("clientUrl", $this->serverCfg->get('clientUrl'));
        $this->assign("clientResUrl", $this->serverCfg->get('clientResUrl'));
        $this->assign("clientVersion", $this->serverCfg->get('clientVersion'));
        $this->assign("gameServerId", $this->logicGameSid);
        $this->display();
    }

}
