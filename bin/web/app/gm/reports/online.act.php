<?php

/* -----------------------------------------------------+
 * 在线统计
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Online extends Page
{
    private $dbh;

    public
        $title = '在线人数统计',
        $platformid,
        $serverid;

    public function __construct()
    {
        parent::__construct();

        $this->setPlatformidServerid();
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
    }

    public function process()
    {
        ///////// TEST /////////
        //
        // GameStat::calcStatOnline($this->platformid, $this->serverid, 0);
        // GameStat::calcStatOnline($this->platformid, $this->serverid, 1);
        // GameStat::calcStatOnline($this->platformid, $this->serverid, 2);
        // GameStat::calcStatOnline($this->platformid, $this->serverid, 3);
        // echo 'ok';
        // exit;
        //
        ///////// TEST /////////

        // 请求响应图表数据
        if(isset($this->input['do']) 
            && $this->input['do'] == 'data'
            && $this->input['time_start'] > 0
            && $this->input['time_end'] > 0
            && $this->input['sid'] > 0
        ){
            echo $this->getData($this->input['sid'], $this->input['time_start'], $this->input['time_end']);
            exit;
        }

        // 页面显示
        $msid = Config::getInstance($this->platformid.'_s'.$this->serverid)
            ->get('merge_server_id');
        if($msid){
            $msid = $this->serverid . ',' . $msid;
            $serverIds = explode(',', $msid);
        }else{
            $serverIds = array($this->serverid);
        }
        $format = '%m-%d %H:%M';
        $type = isset($this->input['type']) ? $this->input['type'] : 'yesterday';
        if($type){
            if($type == 'today'){
                $start = today0clock();
                $end = $start + 86400 - 1;
                $format = '%H:%M';
                $this->title .= ' - 今天';
            }elseif($type == 'yesterday'){
                $start = today0clock() - 86400;
                $end = $start + 86400 - 1;
                $format = '%H:%M';
                $this->title .= ' - 昨天';
            }elseif($type == 'day7'){
                $end = today0clock() - 1;
                $start = $end - 7*86400 + 1;
                $format = '%m月%d日';
                $this->title .= ' - 7天';
            }elseif($type == 'day30'){
                $end = today0clock() - 1;
                $start = $end - 30*86400 + 1;
                $format = '%m月%d日';
                $this->title .= ' - 30天';
            }elseif($type == 'custom'
                && isset($this->input['time_start']) 
                && isset($this->input['time_start'])
                && $this->input['time_start'] != ''
                && $this->input['time_end'] != ''
            ){
                $start = strtotime($this->input['time_start']);
                $end = strtotime($this->input['time_end']);
                if(($end - $start) <= 86400){
                    $format = '%H:%M';
                    $this->title .= ' - 今天';
                }
            }
        }
        $this->assign('serverIds', $serverIds);
        $this->assign('type', $type);
        $this->assign('start', $start);
        $this->assign('end', $end);
        $this->assign('format', $format);
        $this->display();
    }

    private function getData($sid, $start, $end)
    {
        $list = array();
        $diff = $end - $start;
        $m = date('m', $start);
        $d = date('d', $start);
        $y = date('Y', $start);
        $firstCount = 0;
        $isToday = date('Ymd') == date('Ymd', $start) ? true : false;
        if($isToday){
            $sql = "SELECT max(count) as count, floor(time/600)*600 as time1 FROM `log_online`";
            $where = " where time >= $start and time <= $end group by time1 order by time1 asc";
            $data = $this->getList($sql.$where, 'time1');
            if(!count($data)) return '[]';
            // fix data
            $endHour = date('G', $end);
            for($h = 0; $h <= $endHour; $h++){
                for($i = 0; $i <= 50; $i+=10){
                    $s = mktime($h, $i, 0, $m, $d, $y);
                    if(isset($data[$s]) && $data[$s] > 0) 
                    {
                        $firstCount = $data[$s];
                        $list[$s] = $data[$s];
                        continue;
                    }
                    if($firstCount > 0) $list[$s] = 0;
                }
            }
        }else{
            $dbh = Db::getInstance();
            $where = " where hour >= $start and hour <= $end";
            $where .= " and sid = '{$sid}' and pid = '{$this->platformid}'";
            if($diff <= 86400 * 7){
                $sql = "SELECT hour as time, count FROM game_stat_online";
                $data = $this->getList($sql.$where, 'time', $dbh);
                if(!count($data)) return '[]';
                // fix data
                $diffDays = ceil($diff / 86400);
                // 开始统计时间的当天0点
                $clock0 = mktime(0, 0, 0, $m, $d, $y);
                for($n = 0; $n < $diffDays; $n++){
                    $s = $clock0 + $n * 86400;
                    $e = $s + 86400;
                    for($s; $s < $e; $s+=3600){
                        if(isset($data[$s]) && $data[$s] > 0) 
                        {
                            $firstCount = $data[$s];
                            $list[$s] = $data[$s];
                            continue;
                        }
                        if($firstCount > 0) $list[$s] = 0;
                    }
                }
            }else{
                $sql = "SELECT day as time, max(count) as count FROM game_stat_online";
                $where .= ' group by day ';
                $data = $this->getList($sql.$where, 'time', $dbh);
                if(!count($data)) return '[]';
                // fix data
                $diffDays = ceil($diff / 86400);
                // 开始统计时间的当天0点
                $clock0 = mktime(0, 0, 0, $m, $d, $y);
                for($n = 0; $n < $diffDays; $n++){
                    $s = $clock0 + $n * 86400;
                    if(isset($data[$s]) && $data[$s] > 0) 
                    {
                        $firstCount = $data[$s];
                        $list[$s] = $data[$s];
                        continue;
                    }
                    if($firstCount > 0) $list[$s] = 0;
                }
            }
        }
        return $this->getJSON($list);
    }

    private function getList($sql, $timeField = 'time', $dbh = ''){
        if('' == $dbh) $dbh = $this->dbh;
        $rs = $dbh->query($sql);
        $list = array();
        while ($row = $dbh->fetch_array($rs)) {
            $list[$row[$timeField]] = $row['count'];
        }
        return $list;
    }

    private function getJSON($list){
        $comma = '';
        $data = '';
        foreach($list as $k => $v){
            $data .= $comma.'['.$k.'000,'.$v.']';
            $comma = ',';
        }
        return '['.$data.']';
    }

}
