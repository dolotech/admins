<?php

class Act_Is_login extends TencentProxy{

    public function __construct(){
        parent::__construct();
        $this->init_sdk();
    }

    public function process(){
        if(!isset($this->input['openid'])
            || !isset($this->input['openkey'])
            || !isset($this->input['pf'])
            || !isset($this->input['pfkey'])
        ){
            exit('{"ret":2, "msg": "error arg"}');
        }
        $is_login = $this->is_login();
        if($is_login['ret'] != 0){
            exit('{"ret":2, "msg": "'.$is_login['msg'].'"}');
        }
        echo '{"ret":0, "msg": "ok"}';
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
