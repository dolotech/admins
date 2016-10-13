<?php

class Act_Login extends DefaultAPI{

    public function __construct(){
        parent::__construct();
        $this->paramKeys = array(
            'account',
            'act',
            'adult',
            'from',
            'platform',
            'serverid',
            'time',
            'sign',

        );
        $this->debug = true;
        $this->init();
        $this->appKey = $this->platformCfg->get('loginkey');
    }

    public function process(){
        echo Utils::ip2addr(clientIp());
        $this->verifySign();
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
        $this->assign("clientUrl", $this->serverCfg->get('clientUrl'));
        $this->assign("clientResUrl", $this->serverCfg->get('clientResUrl'));
        $this->assign("clientVersion", $this->serverCfg->get('clientVersion'));
        $this->assign("gameServerId", $this->logicGameSid);
        $this->addTemplate('play');
        $this->display();
    }

}
