<?php
/*-----------------------------------------------------+
 * 游戏统计
 *
 * 名词注解：
 *
 * 注册人数：完成账号注册但还未进行角色创建的用户数
 * 创角人数：完成角色创建并进入游戏的用户数
 * 付费次数：发起并完成的订单数（同账号同角色可重复记录）
 * 付费人数：有过充值记录的账号（同账号同角色不重复记录）
 * 二次付费人数：有过两次及以上的充值记录的账号（同账号同角色不重复记录）
 * 总开服数：一个区服记为1
 * 活跃用户：次日有过登陆记录并游戏10分钟以上的用户
 * 付费率：付费人数/创角人数
 * 二次付费率：二次付费人数/付费人数
 * 注收比(ARPU)：收入/注册人数
 * ARPPU：收入/付费人数
 *
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/
 
class GameStat
{

    /**
     * 在指定某天或某天至今的统计
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
     * @param string|int $day 开始时间，如$day=0为今天,$day=1为昨天,...
     * @param string|int $toNow 结束时间是否为"至今"，false为开始时间的当天23:59:59
	 * @return array 统计结果
     */
    public static function countDay($platformId, $serverId, $day, $toNow){
        $start = today0clock() - $day * 86400;
        $end = $toNow ? time() : $start + 86400 - 1;
        return self::countPeriod($platformId, $serverId, $start, $end);
    }

    /**
     * 在指定时间段内的统计
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
     * @param string|int $start 开始时间戳
     * @param string|int $end 结束时间戳
	 * @return array 统计结果
     */
    public static function countPeriod($platformId, $serverId, $start, $end){
        // 注册人数
        $regs = self::countRegistersInPeriod($platformId, $serverId, $start, $end);
        // 创角人数
        $creates = self::countCreatedPlayersInPeriod($platformId, $serverId, $start, $end);
        // 总注册人数
        $regsAll = self::countRegistersToTime($platformId, $serverId, $end);
        // 总创角人数
        $createsAll = self::countCreatedPlayersToTime($platformId, $serverId, $end);
        $chargeStat = ChargeStat::sum($platformId, $serverId, $start, $end);
        $chargeStatAll = ChargeStat::sumToTime($platformId, $serverId, $end);
        // 充值金额
        $money = $chargeStat['moneyAll'];
        // 充值总金额
        $moneyAll = $chargeStatAll['moneyAll'];
        // 充值元宝
        $moneyGame = $chargeStat['moneyGameAll']; 
        // 充值总元宝
        $moneyGameAll = $chargeStatAll['moneyGameAll']; 
        // 付费次数/订单数
        $orders = $chargeStat['orders'];
        // 付费人数
        $payPlayers = $chargeStat['players'];
        // 付费总人数
        $payPlayersAll = $chargeStatAll['players'];
        // 二次付费人数
        $payPlayers2 = $chargeStat['players2'];
        // 创角率
        $rateCreatesRegs = $regs > 0 ? round($creates / $regs * 100, 2) : 0;
        // 付费率(总)
        $ratePay = $createsAll > 0 ? round($payPlayersAll / $createsAll * 100, 2) : 0;
        // 收/注(总)
        $rateMoneyRegs = $regsAll > 0 ? round($moneyAll / $regsAll, 2) : 0;
        // ARPPU
        $arppu = $payPlayers > 0 ? round($money / $payPlayers, 2) : 0;
        return array(
            'regs'           => $regs,
            'creates'        => $creates,
            'regsAll'        => $regsAll,
            'createsAll'     => $createsAll,
            'rateCreatesRegs'=> $rateCreatesRegs,
            'money'          => $money,
            'moneyGame'      => $moneyGame,
            'moneyAll'       => $moneyAll,
            'moneyGameAll'   => $moneyGameAll,
            'orders'         => $orders,
            'payPlayers'     => $payPlayers,
            'payPlayersAll'  => $payPlayersAll,
            'payPlayers2'    => $payPlayers2,
            'ratePay'        => $ratePay,
            'rateMoneyRegs'  => $rateMoneyRegs,
            'arppu'          => $arppu,
        );
    }

	/**
	 * 计算在指定时间段内的注册人数
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
	 * @param string $start 开始时间
	 * @param string $end 结束时间
	 * @return int 统计结果
	 */
    public static function countRegistersInPeriod($platformId, $serverId, $start, $end){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $sql = 'select count(distinct userid) from log_cp_user ';
        $where = "where regtime >= '$start' and regtime <= '$end'";
        $num = $dbh->getOne($sql . $where);
        return $num;
    }

    public static function countRegistersToTime($platformId, $serverId, $time){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $sql = 'select count(distinct userid) from log_cp_user ';
        $where = "where regtime <= '$time'";
        $num = $dbh->getOne($sql . $where);
        return $num;
    }

	/**
	 * 计算在指定时间段内创建的角色数量
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
	 * @param string $start 开始时间
	 * @param string $end 结束时间
	 * @return int 统计结果
	 */
    public static function countCreatedPlayersInPeriod($platformId, $serverId, $start, $end){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $sql = 'select count(id) from log_create_role ';
        $where = "where time >= '$start' and time <= '$end'";
        $num = $dbh->getOne($sql . $where);
        return $num;
    }

    public static function countCreatedPlayersToTime($platformId, $serverId, $time){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $sql = 'select count(id) from log_create_role ';
        $where = "where time <= '$time'";
        $num = $dbh->getOne($sql . $where);
        return $num;
    }

    // CREATE TABLE IF NOT EXISTS `game_stat_day` (
    //   `day` int(11) NOT NULL,
    //   `moneyAll` int(11) NOT NULL,
    //   `moneyGameAll` int(11) NOT NULL,
    //   `regs` int(11) NOT NULL,
    //   `creates` int(11) NOT NULL,
    //   `payPlayers` int(11) NOT NULL,
    //   `payPlayers2` int(11) NOT NULL,
    //   `orders` int(11) NOT NULL,
    //   `ratePay` int(11) NOT NULL,
    //   `rateCreatesRegs` int(11) NOT NULL,
    //   `rateMoneyRegs` int(11) NOT NULL,
    //   `arppu` int(11) NOT NULL,
    //   `activePlayers` int(11) NOT NULL,
    //   PRIMARY KEY (`day`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='每日统计';
    public static function calcStatDay($platformId, $serverId, $day){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $start = today0clock() - $day * 86400;
        $end = $start + 86400 - 1;

        // 如果没有任何登陆记录，则不统计
        $checkSQL = "SELECT count(*) FROM `log_in_out` ";
        $checkSQL .= "WHERE time >= $start and time <= $end";
        $check = $dbh->getOne($checkSQL);
        if(!$check) return 0;

        // 活跃用户：次日有过登陆记录并游戏10分钟以上的用户
        $subSQL = "select player_id from log_create_role where time >= $start and time <= $end";
        $activePlayersSQL = "SELECT count(distinct player_id) FROM `log_in_out` ";
        $activePlayersSQL .= "WHERE time >= $start and time <= $end and last > 600 and player_id not in({$subSQL})";
        $activePlayers = $dbh->getOne($activePlayersSQL);


        $data = self::countDay($platformId, $serverId, $day, false);
        $data['activePlayers'] = $activePlayers;
        // ARPU
        $data['arpu'] = $activePlayers > 0 ? round($data['money'] / $activePlayers, 2) : 0;
        $data['day'] = $start;
        $data['sid'] = $serverId;
        $data['pid'] = $platformId;

        $sqlFieldsArr = array();
        $sqlValuesArr = array();
        foreach($data as $key => $val){
            $sqlFieldsArr[] = '`'.$key.'`';
            $sqlValuesArr[] = '\''.$val.'\'';
        }
        $sqlFields = implode(',', $sqlFieldsArr);
        $sqlValues = implode(',', $sqlValuesArr);
        $sql = "replace into game_stat_day ($sqlFields) values($sqlValues);";
        return Db::getInstance()->exec($sql);
    }

    // Array
    // (
    //     [day] => 20150801
    //     [moneyGame] => 10800
    //     [moneyGameAll] => 99900
    //     [regs] => 144
    //     [regsAll] => 3209
    //     [creates] => 124
    //     [createsAll] => 3021
    //     [payPlayers] => 19
    //     [payPlayersAll] => 160
    //     [payPlayers2] => 19
    //     [activePlayers] => 214
    //     [orders] => 40
    //     [ratePay] => 5.3
    //     [rateCreatesRegs] => 86.11
    //     [rateMoneyRegs] => 2.52
    //     [arpu] => 4.13
    //     [arppu] => 46.55
    //     [money] => 884.5
    //     [moneyAll] => 8099.7
    // )

    public static function getStatDays($platformId, $serverId, $fromDay, $toDay = 1, $sum = false){
        $start = today0clock() - $fromDay * 86400;
        $end = today0clock() - ($toDay - 1) * 86400 - 1;
        return self::getStatInPeriod($platformId, $serverId, $start, $end, $sum);
    }

    public static function getStatInPeriod($platformId, $serverId, $start, $end, $sum = false){
        $dbh = Db::getInstance();
        $data = array();
        $where = ' where 1';
        $where .= $start ? ' and day >= '.$start : '';
        $where .= $end ? ' and day <= '.$end : '';
        if($serverId){
            if(is_array($serverId)){
                $where .= ' and sid in ('. implode(',', $serverId) . ')';
            }else{
                $where .= ' and sid = ' . $serverId;
            }
        }
        $where .= $platformId ? " and pid = '$platformId'" : '';
        if($sum){
            $sql = "select moneyGameAll, regsAll, createsAll, payPlayersAll, moneyAll from game_stat_day ";
            $sql .= $where." order by day desc limit 1";
            $data2 = $dbh->getRow($sql);
            if(!$data2) return false;
            $fields = array (
                // 'day',
                'moneyGame',
                //// 'moneyGameAll',
                'regs',
                //// 'regsAll',
                'creates',
                //// 'createsAll',
                'payPlayers',
                //// 'payPlayersAll',
                'payPlayers2',
                'activePlayers',
                'orders',
                // 'ratePay',
                // 'rateCreatesRegs',
                // 'rateMoneyRegs',
                // 'arpu',
                // 'arppu',
                // 'money',
                //// 'moneyAll',
            );
            $sqlFieldsArr = array();
            $sqlFieldsArr[] = "round(sum(money),2) as money";
            foreach($fields as $field){
                $sqlFieldsArr[] = "sum($field) as $field";
            }
            $sqlFieldsStr = implode(',', $sqlFieldsArr);
            $sql = "select $sqlFieldsStr from game_stat_day ";
            $data1 = $dbh->getRow($sql.$where.' order by day desc');
            ////
            $data = array_merge($data1, $data2);
            $data = self::fix($data);
        }else{
            $sql = 'select * from game_stat_day ';
            $data = $dbh->getAll($sql.$where.' order by day desc');
            if(!$data) return false;
        }
        return $data;
    }

    public static function fix($data){
        // 创角率
        $data['rateCreatesRegs'] = $data['regs'] > 0 ? round($data['creates'] / $data['regs'] * 100, 2) : 0;
        // 付费率(总)
        $data['ratePay'] = $data['createsAll'] > 0 ? round($data['payPlayersAll'] / $data['createsAll'] * 100, 2) : 0;
        // 收/注(总)
        $data['rateMoneyRegs'] = $data['regsAll'] > 0 ? round($data['moneyAll'] / $data['regsAll'], 2) : 0;
        // ARPPU
        $data['arppu'] = $data['payPlayers'] > 0 ? round($data['money'] / $data['payPlayers'], 2) : 0;
        // ARPU
        $data['arpu'] = $data['activePlayers'] > 0 ? round($data['money'] / $data['activePlayers'], 2) : 0;
        return $data;
    }

    public static function runPerDay(){
        $ps = Config::getInstance()->get('platformsServers');
        foreach($ps as $pid => $servers){
            foreach($servers as $sid){
                // 统计昨天的游戏数据
                self::calcStatDay($pid, $sid, 1);
                usleep(300000);
                // 统计昨天的在线数据
                self::calcStatOnline($pid, $sid, 1);
                usleep(300000);
                // 统计昨天的留存数据
                self::calcStatRetentionDay($pid, $sid, 1);
                usleep(300000);
                // 其他的统计，继续在此添加
                GameStat::calcStatLostLevelDay($pid, $sid, 1);
                usleep(300000);
            }
        }
        echo 'ok';
    }
    
    public static function calcStatOnline($platformId, $serverId, $day){
        $dbh = GameDb::getGameDbInstance($platformId, $serverId);
        $time = today0clock() - $day * 86400;
        $m = date('m', $time);
        $d = date('d', $time);
        $y = date('Y', $time);
        $day = mktime(0, 0, 0, $m, $d, $y);
        for($h = 0; $h <= 23; $h++){
            $t1 = mktime($h, 0, 0, $m, $d, $y);
            $t2 = $t1 + 3600 - 1;
            $count = $dbh->getOne("select max(count) from log_online where time >= $t1 and time <= $t2");
            if($count){
                Db::getInstance()->exec("replace into game_stat_online (`hour`, `sid`, `pid`, `day`, `count`) values($t1, '$serverId', '$platformId', $day, $count);");
            }
        }
    }

    // 留存统计
    public static function calcStatRetention($platformId, $serverId, $regDay, $nth){
        $regStart = today0clock() - $regDay * 86400;
        $regEnd = $regStart + 86400 -1;

        $db = Db::getInstance();
        $gameDb = GameDb::getGameDbInstance($platformId, $serverId);

        $whereStatKey = "day = {$regStart} and sid = {$serverId} and pid = '{$platformId}'";
        $isStat = $db->getOne("select count(*) from game_stat_retention where ".$whereStatKey);
        if($isStat){
            // 第$nth日登陆人数
            $loginStart = $regStart + ($nth - 1) * 86400;
            $loginEnd = $loginStart + 86400 -1;
            $whereRegs = "time >= $regStart and time <= $regEnd";
            $regsSql = 'select player_id from log_create_role where '. $whereRegs;
            $whereLogins = "time >= $loginStart and time <= $loginEnd";
            $loginSql = "select count(distinct player_id) from log_in_out where $whereLogins and player_id in ($regsSql)";
            $logins = $gameDb->getOne($loginSql);
            if($logins){
                $db->exec("update game_stat_retention set login{$nth} = $logins where {$whereStatKey}");
            }
        }
    }

    // 留存统计
    public static function calcStatRetentionDay($platformId, $serverId, $day){
        $regStart = today0clock() - $day * 86400;
        $regEnd = $regStart + 86400 -1;

        $db = Db::getInstance();
        $gameDb = GameDb::getGameDbInstance($platformId, $serverId);

        $whereStatKey = "day = {$regStart} and sid = {$serverId} and pid = '{$platformId}'";
        $whereRegs = "time >= $regStart and time <= $regEnd";
        $regsSql = 'select count(*) from log_create_role where '. $whereRegs;
        $regs = $gameDb->getOne($regsSql);
        if($regs){
            $db->exec("REPLACE INTO `game_stat_retention` (`day`, `sid`, `pid`, `regs`) VALUES ('{$regStart}', '{$serverId}', '{$platformId}', '{$regs}');");
        }
        self::calcStatRetention($platformId, $serverId, $day + 1, 2); // 2日留存
        self::calcStatRetention($platformId, $serverId, $day + 2, 3); // 3日留存
        self::calcStatRetention($platformId, $serverId, $day + 3, 4); // 4日留存
        self::calcStatRetention($platformId, $serverId, $day + 4, 5); // 5日留存
        self::calcStatRetention($platformId, $serverId, $day + 5, 6); // 6日留存
        self::calcStatRetention($platformId, $serverId, $day + 6, 7); // 7日留存
        self::calcStatRetention($platformId, $serverId, $day + 14, 15); // 15日留存
        self::calcStatRetention($platformId, $serverId, $day + 29, 30); // 30日留存
    }

    // 流失统计
    public static function calcStatLostLevel($platformId, $serverId, $loginDay, $nth){
        $loginStart = today0clock() - $loginDay * 86400;
        $loginEnd = $loginStart + 86400 -1;

        $db = Db::getInstance();
        $gameDb = GameDb::getGameDbInstance($platformId, $serverId);

        $whereStatKey = " where day = {$loginStart} and sid = {$serverId} and pid = '{$platformId}'";
        $logins = $db->getOne("select logins from game_stat_lost_level ".$whereStatKey);
        if($logins){
            // test sql:
            // SELECT count(distinct player_id) FROM `log_in_out` WHERE time >= 1437735378 and time <= 1437821778
            // SELECT distinct player_id FROM `log_in_out` WHERE time >= 1437735378 and time <= 1437821778
            //
            // 流失的开始时间，总是从登陆结束时间之后开始
            $lostPeriodStart = $loginEnd + 1;
            // 流失的结束时间
            $lostPeriodEnd = $lostPeriodStart + $nth * 86400 - 1;
            if($lostPeriodEnd > time()){
                // 统计时间在未来的时间里，属于无效的统计，直接返回
                return false;
            }
            
            // 流失周期内的所有登陆过游戏的player_id
            $lostPeriodSql = 'select distinct player_id from log_in_out ';
            $lostPeriodSql .= " where time >= $lostPeriodStart and time <= $lostPeriodEnd";
            // 统计不在流失周期内的player_id数量
            $lostCountSql = 'select count(distinct player_id) from log_in_out ';
            $lostCountSql .= " where time >= $loginStart and time <= $loginEnd";
            $lostCountSql .= " and player_id not in($lostPeriodSql)";
            $nologins = $gameDb->getOne($lostCountSql);
            if($nologins){
                // 统计太耗时，这里休息3秒
                usleep(3000000);
                $loginSql = 'select distinct player_id from log_in_out ';
                $loginSql .= " where time >= $loginStart and time <= $loginEnd";
                ///////////
                $lostLevelsSql = 'select count(player_id) as num, level as lev from log_info ';
                $lostLevelsSql .= " where player_id in($loginSql) ";
                $lostLevelsSql .= " and player_id not in($lostPeriodSql) group by level";
                $levelsData = $gameDb->getAll($lostLevelsSql);
                $data = array();
                foreach($levelsData as $row) {
                    $data[$row['lev']] = $row['num'];
                }
                $levels = serialize($data);
                $setSql = "set nologin{$nth} = $nologins";
                $setSql .= ", levels{$nth} = '$levels'";
                $db->exec("update game_stat_lost_level {$setSql} {$whereStatKey}");
                echo '|';
            }
            usleep(1000000);
        }
    }

    // 流失统计
    public static function calcStatLostLevelDay($platformId, $serverId, $day){
        $loginStart = today0clock() - $day * 86400;
        $loginEnd = $loginStart + 86400 -1;

        $db = Db::getInstance();
        $gameDb = GameDb::getGameDbInstance($platformId, $serverId);

        $whereStatKey = "day = {$loginStart} and sid = {$serverId} and pid = '{$platformId}'";
        $whereLogins = "time >= $loginStart and time <= $loginEnd";
        $loginsSql = 'select count(distinct player_id) from log_in_out where '. $whereLogins;
        $logins = $gameDb->getOne($loginsSql);
        if($logins){
            $db->exec("REPLACE INTO `game_stat_lost_level` (`day`, `sid`, `pid`, `logins`) VALUES ('{$loginStart}', '{$serverId}', '{$platformId}', '{$logins}');");
        }
        self::calcStatLostLevel($platformId, $serverId, $day + 1, 1); // 1日流失
        self::calcStatLostLevel($platformId, $serverId, $day + 3, 3); // 3日流失
        self::calcStatLostLevel($platformId, $serverId, $day + 7, 7); // 7日流失
        self::calcStatLostLevel($platformId, $serverId, $day + 15, 15); // 15日流失
        self::calcStatLostLevel($platformId, $serverId, $day + 30, 30); // 30日流失
    }

}
