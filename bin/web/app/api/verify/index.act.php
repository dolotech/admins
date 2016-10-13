<?php
/*-----------------------------------------------------+
 * 充值验证处理
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

// 充值后服务端验证请求：
// http://charge.my/charge.php?mod=verify&platform=tb&time=1401766007&sign=b8a369085fae67a8e0525551caae95c8&myOrderId=14512cb3e-cdd7-fad3-2916-4fd26f9b9526
//
// 参数：
// platform : 平台标识
// time : 时间
// sign : md5(platform + time + key)
// myOrderId : 客户端传上来的订单数据（收据）

// Return Code:
// 0:成功
// 1:订单已经验证过
// 2:订单号格式错语
// 3:签名错误
// 4:签名失效
// 5:参数不足
// 6:订单不存在

class Act_Index extends Page{

    public function __construct(){
        parent::__construct();
    }

    public function process(){
        $my_key = 'oyKhRmKDDd8lA1AASD6FxlrrOjWlZ'; 
        $platform = addslashes($_GET['platform']);
        $myOrderId = addslashes($_GET['myOrderId']);
        $myOrderData = explode('-', $myOrderId);
        if(count($myOrderData) != 4){
            echo json_encode(array('status' => 2));
            return;
        }
        $get_time = $_GET['time'];
        $get_sign = $_GET['sign'];
        if(!$get_sign || !$get_time || !$myOrderId || !$platform){
            echo json_encode(array('status' => 5));
            return;
        }
        $my_time = time();
        if(($my_time - $get_time) > 3600){
            echo json_encode(array('status' => 4));
            return;
        }
        $my_sign = md5($platform . $get_time . $my_key . $myOrderId);
        if($get_sign != $my_sign){
            $this->log('my_sign:'.$my_sign."\nin_sign:".$get_sign."\n");
            echo json_encode(array('status' => 3));
            return;
        }
        $db = Db::getInstance();
        $row = $db->getRow("select `isVerified`, `money` from charge_order where platformName = '$platform' and myOrderId = '$myOrderId'");
        if(!$row){
            echo json_encode(array('status' => 6));
            return;
        }
        $money = (int)$row['money'];
        if($row['isVerified']){
            $status = 1;
        }else{
            $status = 0;
            $db->exec("update charge_order set `isVerified` = 1 where myOrderId = '$myOrderId'");
        }
        echo json_encode(array('status' => $status, 'money' => $money));
    }

    public function log($content){
        $requestInformation = $_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'].', http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']."\n";
        $file = fopen('./log/verify_'.$_REQUEST['platform'].'_26076116.txt',"a+");
        fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        fclose($file); 
    }

}
