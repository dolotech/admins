<?php

/*-----------------------------------------------------+
 * 游戏统计
 *
 * @author rolong@<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class Act_Game_stat_lost_level extends Page{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        if(isset($this->input['do']) && $this->input['do'] == 'chart'){
            $nth = $this->input['nth'];
            $where = "where day = {$this->input['day']}";
            $where .= " and sid = {$this->input['sid']}";
            $where .= " and pid = '{$this->input['pid']}'";
            $row = Db::getInstance()->getRow("select day, logins, nologin{$nth}, levels{$nth} as levels from game_stat_lost_level ".$where.' limit 1');
            $data = unserialize($row['levels']);
            arsort($data);
            $title = date('m月d日', $row['day'])." ".$nth."日流失等级分布图";
            $this->assign('title', $title);
            $this->assign('data', $data);
            $this->addTemplate('game_stat_lost_level_chart');
            $this->display();
            exit;
        }
        $servers = isset($this->input['servers']) ? $this->input['servers'] : array();
        if(isset($this->input['time_start']) 
            && $this->input['time_start'] != ''
            && isset($this->input['time_end'])
        ){
            $timeStart = strtotime($this->input['time_start']);
            if($this->input['time_end']){
                $timeEnd = strtotime($this->input['time_end']);
            }else{
                $timeEnd = $timeStart + 86400 - 1;
            }
            $timeStart2 = $this->input['time_start'];
            $timeEnd2 = $this->input['time_end'];
        }else{
            $timeEnd = today0clock() - 1;
            $timeStart = $timeEnd - 86400 * 2 + 1;
            $timeStart2 = date('Y-m-d H:i:s', $timeStart);
            $timeEnd2 = '';
        }
        $data = array();
        if(count($servers)){
            $platformsStr = '';
            $serversStr = '';
            foreach($servers as $pid => $sids){
                if($platformsStr == ''){
                    $platformsStr = "'$pid'";
                }else{
                    $platformsStr .= ",'$pid'";
                }
                foreach($sids as $sid => $val){
                    $msid = Config::getInstance($pid.'_s'.$sid)->get('merge_server_id');
                    if($msid){
                        $sid = $sid . ',' . $msid;
                    }
                    if($serversStr == ''){
                        $serversStr = $sid;
                    }else{
                        $serversStr .= ','.$sid;
                    }
                }
            }
            $where = " day >= $timeStart and day <= $timeEnd";
            $where .= " and sid in ($serversStr)";
            $where .= " and pid in ($platformsStr)";
            $data = Db::getInstance()->getAll("select day, sid, pid, logins, nologin1, nologin3, nologin7, nologin15, nologin30 from game_stat_lost_level where ".$where.' limit 500');
        }
        $weekarray=array("日","一","二","三","四","五","六");
        $this->assign('weekarray', $weekarray);
        $this->assign('data', $data);
        $this->assign('timeStart', $timeStart2);
        $this->assign('timeEnd', $timeEnd2);
        $this->assign('selectedServers', $servers);
        $this->display();
    }
}
