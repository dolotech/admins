<?php

class Act_Login extends TencentProxy{

    public function __construct(){
        parent::__construct();
        // echo '<pre>';
        // print_r($_SERVER);
        // print_r($_REQUEST);
        // echo '</pre>';
        $this->init_sdk();
    }

    public function process(){
        $is_login = $this->is_login();
        if($is_login['ret'] != 0){
            exit('error: '.$is_login['msg']);
        }
        $userInfo = $this->getUserInfo();
        if($userInfo['ret'] != 0){
            exit('error: '.$userInfo['msg']);
        }
        // http://119.29.103.55
        $baseParams = "&openid={$this->input['openid']}&openkey={$this->input['openkey']}&pf={$this->input['pf']}&pfkey={$this->input['pfkey']}";
        $payurl = "/api/?mod=tencent_proxy&act=payment_url&serverid={$this->input['serverid']}".$baseParams;
        $wordFilterUrl = "/api/?mod=tencent_proxy&act=word_filter".$baseParams;
        $platformArgs = $userInfo['is_yellow_vip'].'|'.$userInfo['is_yellow_year_vip'].'|'.$userInfo['yellow_vip_level'].'|'.$userInfo['is_yellow_high_vip'].'|0';
        $args = array(
            'openid'    => $this->input['openid'],
            'openkey'   => $this->input['openkey'],
            'pf'        => $this->input['pf'],
            'pfkey'     => $this->input['pfkey'],
            'mod'       => 'tencent',
            'act'       => 'login',
            'adult'     => 1,
            'from'      => 'tencent',
            'platform'  => 'tencent',
            'serverid'  => $this->input['serverid'],
            'time'      => TIMESTAMP,
            'payurl'    => $payurl,
            'extra'     => $platformArgs,
            // 'sandbox'   => SANDBOX ? 1 : 0,
            'sandbox'   => 0,
            'filterurl' => $wordFilterUrl,
        );
        $this->setSignData($args);
        $args['sign'] = $this->getMySign();
        $query = http_build_query($args);
        // http://119.29.103.55
        $url = "http://{$this->dstHost}/api/?{$query}";
        // $url = "http://127.0.0.1/api/entry.php?{$query}";
        //初始化curl
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        echo($output);
        ///////////////////////////
        // 记录来自集市任务的用户
        ///////////////////////////
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
