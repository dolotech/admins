<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/
 
class Game
{
    public static function checkPlatformServer($platformId, $serverId){
        return file_exists(CFG_DIR.'/'.$platformId.'_s'.$serverId.'.cfg.php');
    }

    public static function platformsForm($platformId){
        $ps = Config::getInstance()->get('platformsServers');
        $platformsOpt = array();
        $serversOpt = array();
        foreach($ps as $pid => $servers){
            $platformsOpt[$pid] = self::platform($pid) . '　';
        }
        $extra = 'onchange="document.getElementById(\'platformsForm\').submit();"';
        $extra .= ' class="am-input-sm" ';
        $html = '';
        $html .= '<form id="platformsForm" class="am-form am-form-inline" name="platformsForm" method="POST">';
        $html .= '<div class="am-form-group">';
        $html .= Form::select('platformid', $platformsOpt, $platformId, false, $extra);
        $html .= '</div>';
        $html .= '</form>';
        return $html;
    }

    public static function platformsServersForm($platformId, $serverId){
        $ps = Config::getInstance()->get('platformsServers');
        $platformsOpt = array();
        $serversOpt = array();
        foreach($ps as $pid => $servers){
            $platformsOpt[$pid] = self::platform($pid) . '　';
            if($platformId == $pid){
                $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
                foreach($servers as $sid){
                    if(isset($tgsid2lgsids[$sid])){
                        $startSid = $tgsid2lgsids[$sid][0];
                        $end = count($tgsid2lgsids[$sid]) - 1;
                        $endSid = $tgsid2lgsids[$sid][$end];
                        if($startSid == 2001) $startSid = 0;
                        $name = $startSid.'~'.$endSid;
                    }else{
                        $name = $sid;
                    }
                    $serversOpt[$sid] = $name . '区　';
                }
            }
        }
        $extra = 'onchange="document.getElementById(\'platformsServersForm\').submit();"';
        $extra .= ' class="am-input-sm" ';
        $html = '';
        $html .= '<form id="platformsServersForm" class="am-form am-form-inline" name="platformsServersForm" method="POST">';
        $html .= '<div class="am-form-group">';
        $html .= Form::select('platformid', $platformsOpt, $platformId, false, $extra);
        $html .= '</div>';
        $html .= '<div class="am-form-group">';
        $html .= Form::select('serverid', $serversOpt, $serverId, false, $extra);
        $html .= '</div>';
        $html .= '</form>';
        return $html;
    }

    public static function getDefaultPlatformServer($platformId = '', $serverId = ''){
        $ps = Config::getInstance()->get('platformsServers');
        list($platformId, $servers) = each($ps);
        return array(0 => $platformId, 1 => $servers[0]);
    }

    // 获得全部分区(不包括被逻辑合服的分区)
    public static function getPlatformsServers(){
        $ps = Config::getInstance()->get('platformsServers');
        $rt = array();
        foreach($ps as $pid => $servers){
            $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
            foreach($servers as $sid){
                if(isset($tgsid2lgsids[$sid])){
                    $startSid = $tgsid2lgsids[$sid][0];
                    $end = count($tgsid2lgsids[$sid]) - 1;
                    $endSid = $tgsid2lgsids[$sid][$end];
                    if($startSid == 2001) $startSid = 0;
                    $name = $startSid.'~'.$endSid;
                }else{
                    $name = $sid;
                }
                $rt[$pid][$sid] = $name;
            }
        }
        return $rt;
    }

    // 获得全部分区(不包括被物理合服的分区)
    public static function getPlatformsServers2(){
        $ps = Config::getInstance()->get('platformsServers');
        $rt = array();
        foreach($ps as $pid => $servers){
            $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
            $lgsid2tgsid = Config::getInstance($pid)->get('lgsid2tgsid');
            foreach($servers as $sid){
                if(isset($lgsid2tgsid[$sid])){
                    continue;
                }
                if(isset($tgsid2lgsids[$sid])){
                    $startSid = $tgsid2lgsids[$sid][0];
                    $end = count($tgsid2lgsids[$sid]) - 1;
                    $endSid = $tgsid2lgsids[$sid][$end];
                    if($startSid == 2001) $startSid = 0;
                    $name = $startSid.'~'.$endSid;
                }else{
                    $name = $sid;
                }
                $rt[$pid][$sid] = $name;
            }
        }
        return $rt;
    }

    public static function platform($id){
        $data = array(
            'tencent' => '腾讯',
            'swjoy' => '顺网',
            'kuaiwan' => '快玩',
            'tencent_sandbox' => '腾讯沙箱',
            'test' => '测试',
        );
        if(isset($data[$id])){
            return $data[$id];
        }
        return $id;
    }

    public static function chatType($type){
        $data = array(
            1 => '世界',
            2 => '场景',
            3 => '帮派',
            4 => '队伍',
            5 => '私聊',
            6 => '系统',
            7 => '喇叭',
        );
        if(isset($data[$type])){
            return $data[$type];
        }
        return '未知';
    }

    public static function race($type){
        $data = array(
            1 => '草栗子',
            2 => '白衣絮',
            3 => '铁魔',
            4 => '封瑶儿',
        );
        if(isset($data[$type])){
            return $data[$type];
        }
        return '未知';
    }

    public static function status($type, $time){
        if($time > 0 && time() > $time){
            return array('text' => '正常', 'color' => '#00ccff', 'status' => $type);
        }
        $data = array(
            0 => array('text' => '正常', 'color' => '#000000', 'status' => $type),
            1 => array('text' => '封停', 'color' => '#ff3300', 'status' => $type),
            2 => array('text' => '解封', 'color' => '#0033ff', 'status' => $type),
            3 => array('text' => '封IP', 'color' => '#ff0000', 'status' => $type),
            4 => array('text' => '解IP', 'color' => '#0000ff', 'status' => $type),
            5 => array('text' => '禁言', 'color' => '#ff6600', 'status' => $type),
            6 => array('text' => '解禁', 'color' => '#0066ff', 'status' => $type),
        );
        if(isset($data[$type])){
            return $data[$type];
        }
        return array('text' => '正常', 'color' => '#0099ff', 'status' => $type);
    }

    public static function getLevel($id, $dbh, $platform){
        if(is_numeric($dbh)) $dbh = GameDb::getGameDbInstance($platform, $dbh);
        $level = $dbh->getOne("select level from {$dbh->dbname}.log_level where player_id = '$id' order by id desc");
        if(!$level) return 0;
        return $level;
    }

    public static function isOnline($id, $dbh, $platform){
        if(is_numeric($dbh)) $dbh = GameDb::getGameDbInstance($platform, $dbh);
        $type = $dbh->getOne("select type from {$dbh->dbname}.log_in_out where player_id = '$id' order by id desc");
        if($type == 1) return 1;
        return 0;
    }

    public static function getStatus($id, $dbh, $platform){
        if(is_numeric($dbh)) $dbh = GameDb::getGameDbInstance($platform, $dbh);
        $row = $dbh->getRow("select status, status_time from {$dbh->dbname}.log_info where player_id = '$id'");
        return self::status($row['status'], $row['status_time']);
    }

    public static function setStatus($id, $status, $time, $dbh, $platform = ''){
        if(!$platform) $platform = $_COOKIE['platformid'];
        if(is_numeric($dbh)) $dbh = GameDb::getGameDbInstance($platform, $dbh);
        return $dbh->exec("update {$dbh->dbname}.log_info set `status` = '$status', `status_time` = '$time' where player_id = '$id'");
    }

    public static function getName($id, $dbh, $platform = ''){
        if(is_numeric($dbh)) {
            if(!$platform) $platform = $_COOKIE['platformid'];
            $dbh = GameDb::getGameDbInstance($platform, $dbh);
        }
        $name = $dbh->getOne("select name from {$dbh->dbname}.log_info where player_id = '$id'");
        if(!$name) return 'Undef';
        return $name;
    }

    public static function getGoodsName($id){
        $dbh = Db::getInstance();
        $name = $dbh->getOne("select goods_name from game.log_goods where goodsid = '$id'");
        if(!$name) return 'Undef';
        return $name;
    }

    public static function getGoods($id){
        $dbh = Db::getInstance();
        $row = $dbh->getRow("select * from game.log_goods where goodsid = '$id'");
        if(!$row) return false;
        return $row;
    }

    public static function items2string($items){
        $items = is_string($items) ? unserialize($items) : $tiems;
        $result = array();
        foreach($items as $type => $data){
            if('ext' == $type){
                foreach($data as $k => $v){
                    switch($k){
                    case 1 :
                        $result[] = '元宝：'.$v;
                        break;
                    case 2 :
                        $result[] = '礼金：'.$v;
                        break;
                    case 3 :
                        $result[] = '银两：'.$v;
                        break;
                    }
                }
            }elseif('goods' == $type){
                foreach($data as $id => $num){
                    $name = self::getGoodsName($id);
                    $result[] = $name.'：'.$num;
                }
            }
        }
        return implode('<br />', $result);

    }

    public static function rewardTypes(){
        return array(
            '901' => '维护补偿',
            '902' => 'BUG补偿',
            '903' => '活动异常补偿',
            '904' => '奖励补发',
            '905' => '充值补发',
            '906' => '活动奖励',
            '907' => '帮派奖励',
            '908' => '论坛活动奖励',
            '999' => '后台奖励',
        );
    }

    public static function getRewardTypeName($id){
        $data = self::rewardTypes();
        return isset($data[$id]) ? $data[$id] : '';
    }

    public static function servers2string($servers){
        $servers = is_string($servers) ? unserialize($servers) : $tiems;
        $result = array();
        foreach($servers as $pid => $servers){
            $sids = implode(',', $servers);
            $platform = self::platform($pid);
            $result[] = $platform . '：' . $sids;
        }
        return implode('<br />', $result);

    }

    //  'lgsid2tgsid' => array(
    //      2 => 1, 
    //      3 => 1,
    //      4 => 1,
    //      5 => 1,
    //  ),
    //  // 目标游戏服务器ID -> 包含的逻辑游戏服务器ID
    //  'tgsid2lgsids' => array(
    //  ),
    // 获取被逻辑合服的目标服务器ID
    public static function getTargetServerId($platformid, $lgsid) {
        $lgsid2tgsid = Config::getInstance($platformid)->get('lgsid2tgsid');
        if(isset($lgsid2tgsid[$lgsid])){
            $tgsid2lgsids = Config::getInstance($platformid)->get('tgsid2lgsids');
            $tgsid = $lgsid2tgsid[$lgsid];
            if(isset($tgsid2lgsids[$tgsid])){
                $lgsids = $tgsid2lgsids[$tgsid];
                if(isset($lgsids[$lgsid])){
                    return $tgsid;
                }
            }
        }
        return $lgsid;
    }

    // 获取被物理合服的目标服务器ID
    public static function getTargetServerId2($platformid, $lgsid) {
        $lgsid2tgsid = Config::getInstance($platformid)->get('lgsid2tgsid');
        if(isset($lgsid2tgsid[$lgsid])){
            return $lgsid2tgsid[$lgsid];
        }
        return $lgsid;
    }

    // 腾讯服务器ID转换为游戏逻辑服务器ID
    public static function tencentSidToGameSid($tencentSid) {
        if($tencentSid >= 9 && $tencentSid <= 21){
            return $tencentSid - 8;
        }elseif($tencentSid >= 26){
            return $tencentSid - 16;
        }
        return 0;
    }

    // 游戏逻辑服务器ID转换为腾讯服务器ID
    public static function gameSidToTencentSid($gameSid) {
        if($gameSid >= 1 && $gameSid <= 13){
            return $gameSid + 8;
        }elseif($gameSid >= 14){
            return $gameSid + 16;
        }
        return 0;
    }
}
