<?php

class Act_Index extends Page{

    public $platformCfg = array();
    public $serverCfg = array();
    public $serverid = '';
    public $username = '';

    public function __construct(){
        parent::__construct();
        $this->platformCfg = Config::getInstance('test');
    }

    public function process(){
        if(isset($this->input['serverid']) && isset($this->input['username'])
            && $this->input['serverid'] > 0 && $this->input['username'] != ''){
            $this->serverid = $this->input['serverid'];
            $this->username = $this->input['username'];
            $this->serverCfg = Config::getInstance('test_s'.$this->serverid);
            $this->login();
        }else{
            $serverIds = $this->platformCfg->get('serverIds');
            $this->assign('serverIds', $serverIds);
            $this->display();
        }
    }

    public function login() {
        $args = array(
            0  => $this->serverCfg->get('serverIP'),
            1  => $this->serverCfg->get('serverPort'),
            2  => $this->serverid, // 游戏服务器ID
            3  => $this->username, // 账号,
            4  => 1, // 是否成年
            5  => 'http://www.baidu.com',
            6  => '', // 礼包URL
            7  => TIMESTAMP,
            8  => '', // 收藏地址
            9  => 'tencent', // 平台ID
            10 => '',
        );
        $info = base64_encode(implode(',', $args));
        $sign = md5(TIMESTAMP . $this->platformCfg->get('appkey'));
        $relase['info'] = $info;
        $relase['sign'] = $sign;
        $relase['time'] = TIMESTAMP;
        $query = http_build_query($relase);
        $url = $this->serverCfg->get('urlBase').'?'.$query;
        header('Refresh: 0; url='.$url);
    }

}
