<?php
/*-----------------------------------------------------+
 * 网站管理入口模块 
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Test extends Page{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        exit();
        $ps = Config::getInstance()->get('platformsServers');
        foreach($ps as $pid => $servers){
            foreach($servers as $sid){
                for($i = 80; $i > 0; $i--){
                    //echo $pid.'='.$sid.'-'.$i.'|';
                    GameStat::calcStatRetention($pid, $sid, $i);
                    // usleep(30000);
                }
                // $msid = Config::getInstance($pid.'_s'.$sid)->get('merge_server_id');
                // if($msid){
                //     $msid = $sid . ',' . $msid;
                //     $sid2 = explode(',', $msid);
                // }else{
                //     $sid2 = array($sid);
                // }
                // foreach($sid2 as $s){
                //     for($i = 60; $i > 0; $i--){
                //         echo $s.'|';
                //         GameStat::calcStatOnline($pid, $s, $i);
                //         // usleep(100000);
                //     }
                // }
            }
        }
    }
}

// //初始化
// $ch = curl_init();
// $urls = array();
// foreach($urls as $u){
//     //设置选项，包括URL
//     curl_setopt($ch, CURLOPT_URL, $u);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_HEADER, 0);
//     //执行并获取HTML文档内容
//     echo $output = curl_exec($ch);
// }
// //释放curl句柄
// curl_close($ch);
// exit;
