<?php

class Act_Word_filter extends TencentProxy{

    public function __construct(){
        parent::__construct();
        $this->init_sdk();
    }

    public function process(){
        $this->show(1);
        return;
        // $this->log("SERVER: \n".var_export($_SERVER, TRUE), true);
        if(!isset($this->input['openid'])
            || !isset($this->input['openkey'])
            || !isset($this->input['pf'])
            || !isset($this->input['pfkey'])
            || !isset($this->input['is_check'])
            || !isset($this->input['content'])
        ){
            $this->show(0);
            exit();
        }
        // $this->input['content'] = urlencode(iconv('GB2312', 'UTF-8', $this->input['content']));
        // $this->input['content'] = urlencode($this->input['content']);
        $rt = $this->filter();
        if($rt['ret'] != 0 || $rt['is_dirty'] != 0){
            $this->show(0, $rt['msg']);
            exit;
        }
        $this->show(1);
    }

    public function show($isOK, $content = '') {
        if($isOK){
            if($_GET['is_check']){
                echo '1';
            }else{
                if($content) echo $content;
                else echo $_GET['content'];
            }
        }else{
            if($_GET['is_check']){
                echo '0';
            }else{
                if($content) echo $content;
                else echo '***';
            }
        }
    }

    // http://119.29.103.55/api/?mod=tencent_proxy&act=word_filter&openid=06F1342FC6DA124270FA329676213FC1&openkey=BB534AC8D9322C29C9430342304F6126&pf=website&pfkey=246c420ad00826210019fcf37d57a5d8&content=aaaaaa
    public function filter() {
        $params = array(
            'openid' => $this->input['openid'],
            'openkey' => $this->input['openkey'],
            'pf' => $this->input['pf'],
            'pfkey' => $this->input['pfkey'],
            'format' => 'json',
            'content' => $this->input['content'],
            'msgid' => TIMESTAMP,
        );
        $script_name = '/v3/csec/word_filter';
        return $this->sdk->api($script_name, $params, 'post', $this->protocol);
    }

}
