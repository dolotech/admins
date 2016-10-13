<?php

class Act_Payment_url extends TencentAPI{

    public $sdk;

    public function __construct(){
        parent::__construct();
        if(!isset($this->input['serverid'])){
            exit('{"ret":1, "msg": "no serverid"}');
        }
        $this->sdk = new OpenApiV3($this->platformCfg->get('appid'), $this->platformCfg->get('appkey'));
        $this->sdk->setServerName($this->platformCfg->get('apiserver'));
    }

    public function process(){
        $payment = $this->payment();
        if($payment['ret'] != 0){
            exit('{"ret":2, "msg": "'.$payment['msg'].'"}');
        }
        echo '{"ret":0, "url": "'.$payment['url_params'].'"}';
    }

    // array (
    //   'ret' => 0,
    //   'url_params' => '/v1/san/1102857043/qz_goods_info?token_id=71CA07C5024B647536FC779FD5B133A327357&sig=0kFnIO%2Bw%2FWZyztblAKSqyazMpug%3D&appid=1102857043',
    //   'token' => '71CA07C5024B647536FC779FD5B133A327357',
    // )

    public function payment(){
        // 1	充值100元宝   	100	    元宝*100	buyyb
        // 2	充值500元宝	    500	    元宝*500	buyyb
        // 3	充值2000元宝	2000	元宝*2000	buyyb
        // 4	充值10000元宝	10000	元宝*10000	buyyb
        // 5	充值20000元宝	20000	元宝*20000	buyyb
        $shopId = $this->input['shop_id'];
        switch($shopId){
        case '1':
            $payment =  '1*100*1';
            $goodsmeta =  '100元宝*100元宝';
            break;
        case '2':
            $payment =  '2*500*1';
            $goodsmeta =  '500元宝*500元宝';
            break;
        case '3':
            $payment =  '3*2000*1';
            $goodsmeta =  '2000元宝*2000元宝';
            break;
        case '4':
            $payment =  '4*10000*1';
            $goodsmeta =  '10000元宝*10000元宝';
            break;
        case '5':
            $payment =  '5*20000*1';
            $goodsmeta =  '20000元宝*20000元宝';
            break;
        default:
            if(isset($this->config['goods'][$shopId])){
                $goods = $this->config['goods'][$shopId];
                $payment = $shopId.'*'.$goods['price'].'*1';
                $goodsmeta =  $goods['name'].'*'.$goods['name'];
            }else{
                exit('error');
            }
        }
        $params = array(
            'openid' => $this->input['openid'],
            'openkey' => $this->input['openkey'],
            'pf' => $this->input['pf'],
            'pfkey' => $this->input['pfkey'],
            // 'amt' => '1000',
            'ts' => time(),
            'payitem' => $payment,
            'appmode' => 2,
            'max_num' => 100000,
            'goodsmeta' => $goodsmeta,
            'goodsurl' => 'http://182.254.229.158/icon/'.$shopId.'_b.png',
            'zoneid' => $this->input['serverid'],
        );
        $script_name = '/v3/pay/buy_goods';
        return $this->sdk->api($script_name, $params, 'post', $this->protocol);
    }

}
