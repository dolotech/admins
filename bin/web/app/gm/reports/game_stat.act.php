<?php

/*-----------------------------------------------------+
 * 游戏统计
 *
 * @author rolong@<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class Act_Game_stat extends Page{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        $period = isset($this->input['period']) ? $this->input['period'] : array();
        $servers = isset($this->input['servers']) ? $this->input['servers'] : array();
        $timeStart = '';
        $timeEnd = '';
        $timeStart2 = '';
        $timeEnd2 = '';
        if(isset($this->input['time_start']) && isset($this->input['time_end'])){
            $timeStart = strtotime($this->input['time_start']);
            $timeEnd = strtotime($this->input['time_end']);
            $timeStart2 = $this->input['time_start'];
            $timeEnd2 = $this->input['time_end'];
        }
        $data = array();
        $isSum = isset($this->input['perday']) ? false : true;
        foreach($servers as $pid => $sids){
            $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
            foreach($sids as $sid => $val){
                $msid = "";
                if(isset($tgsid2lgsids[$sid])){
                    $msid = Config::getInstance($pid.'_s'.$sid)->get('merge_server_id');
                }
                if($msid){
                    $msid = $sid . ',' . $msid;
                    $sid = $msid;
                    $sid2 = explode(',', $msid);
                }else{
                    $sid2 = $sid;
                }
                if($timeStart && $timeEnd){
                    $d = GameStat::getStatInPeriod($pid, $sid2, $timeStart, $timeEnd, $isSum);
                    if($d) $data[$pid][$sid]['custom'] = $d;
                }
                if(in_array('yesterday', $period)){
                    $d = GameStat::getStatDays($pid, $sid2, 1, 1, $isSum);
                    if($d) $data[$pid][$sid]['yesterday'] = $d;
                }
                if(in_array('week', $period)){
                    $d = GameStat::getStatDays($pid, $sid2, 8, 1, $isSum);
                    if($d) $data[$pid][$sid]['week'] = $d;
                }
                if(in_array('all', $period)){
                    $d = GameStat::getStatDays($pid, $sid2, 1000, 1, $isSum);
                    if($d) $data[$pid][$sid]['all'] = $d;
                }
            }
        }
        $weekarray=array("日","一","二","三","四","五","六");  
        $this->assign('weekarray', $weekarray);
        $this->assign('data', $data);
        $this->assign('period', $period);
        $this->assign('timeStart', $timeStart2);
        $this->assign('timeEnd', $timeEnd2);
        $this->assign('selectedServers', $servers);
        $this->display();
    }
}
