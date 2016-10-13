<?php

class Act_Task_callback extends TencentProxy{


    public function __construct(){
        parent::__construct();
    }

    public function process(){
        if(!isset($this->input['openid'])){
            exit('{"ret":103,"msg":"no openid","zoneid":""}');
        }
        if(!isset($this->input['contractid'])){
            exit('{"ret":103,"msg":"no contractid","zoneid":""}');
        }
        $openid = $this->input['openid'];
        $contractid = $this->input['contractid'];
        $sql = "select max(serverid) from tencent_task where openid = '{$openid}' and contractid = '{$contractid}';";
        $serverid = Db::getInstance()->getOne($sql);
        if(!$serverid){
            exit('{"ret":103,"msg":"no serverid","zoneid":""}');
        }

        // task_callback:array (
        //   'appid' => '1102857043',
        //   'billno' => '3C732B7B77A699B5A36A3EDD2AF06D7E_1102857043T320150626151031_1',
        //   'cmd' => 'award',
        //   'contractid' => '1102857043T320150626151031',
        //   'openid' => '3C732B7B77A699B5A36A3EDD2AF06D7E',
        //   'payitem' => '10001',
        //   'pf' => 'qzone',
        //   'providetype' => '2',
        //   'sig' => 'D3/iw4/OMI11kQvIxFdqOtEC7Ko=',
        //   'step' => '1',
        //   'ts' => '1435638423',
        //   'version' => 'V3',
        //   'mod' => 'tencent',
        //   'act' => 'task_callback',
        // )

        $args = array(
            'appid'      => $this->input['appid'      ], 
            'billno'     => $this->input['billno'     ], 
            'cmd'        => $this->input['cmd'        ], 
            'contractid' => $this->input['contractid' ], 
            'openid'     => $this->input['openid'     ], 
            'payitem'    => $this->input['payitem'    ], 
            'pf'         => $this->input['pf'         ], 
            'providetype'=> $this->input['providetype'], 
            'sig'        => $this->input['sig'        ], 
            'step'       => $this->input['step'       ], 
            'ts'         => $this->input['ts'         ], 
            'version'    => $this->input['version'    ], 
            'serverid'   => $serverid,
            'mod' => 'tencent',
            'act' => 'task_callback',
        );
        $query = http_build_query($args);
        // http://119.29.103.55
        $url = "http://{$this->dstHost}/api/?{$query}";
        // Logger::i($url);
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
        echo ($output);
    }
}
