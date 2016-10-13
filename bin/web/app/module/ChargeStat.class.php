<?php
/*-----------------------------------------------------+
 * 充值统计
 *
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class ChargeStat
{

	/**
	 * 获得所有平台的所有分区的充值统计
	 * @return array 统计结果
	 */
    public static function getStat(){
        $ps = Config::getInstance()->get('platformsServers');
        $data = array();
        foreach($ps as $pid => $servers){
            foreach($servers as $sid){
                $data[$pid][$sid]['today'] = self::sumDay($pid, $sid, 0, true);
                $data[$pid][$sid]['yesterday'] = self::sumDay($pid, $sid, 1, false);
                // $data[$pid][$sid]['week'] = self::sumDay($pid, $sid, 7, true);
                // $data[$pid][$sid]['all'] = self::sumDay($pid, $sid, 1000, true);
            }
        }
        return $data;
    }

	/**
	 * 在指定某天或某天至今的充值统计
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
	 * @param string|int $day 开始时间，如$day=0为今天,$day=1为昨天,...
	 * @param string $toNow 结束时间是否为"至今"，false为开始时间的当天23:59:59
	 * @return array 统计结果
	 */
    public static function sumDay($platformId, $serverId, $day, $toNow = false){
        $start = today0clock() - $day * 86400;
        $end = $toNow ? time() : $start + 86400 - 1;
        return self::sum($platformId, $serverId, $start, $end);
    }

	/**
	 * 在指定分区及时间段内的充值统计
	 * @param string $platformId 平台ID
	 * @param string|int $serverId 分区ID
	 * @param string $start 开始时间
	 * @param string $end 结束时间
	 * @return array 统计结果: moneyAll moneyGameAll orders players players2
	 */
    public static function sum($platformId, $serverId, $start, $end){
        $sids = self::getMergeSids($platformId, $serverId);
        $dbh = Db::getInstance();
        $sql = 'select sum(money) as moneyAll, sum(moneyGame) as moneyGameAll, ';
        $sql .= 'count(account) as orders, count(distinct account) as players ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime >= '$start' and ctime <= '$end'";
        $data = $dbh->getRow($sql . $where);
        // 获得2次付费用户数
        $sql = 'select count(distinct account) as players2 ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime >= '$start' and ctime <= '$end' and isFirst = 0";
        $players2 = $dbh->getOne($sql . $where);
        // 获得3次付费用户数
        $sql = 'select count(distinct account) as players3 ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime >= '$start' and ctime <= '$end' and chargeNth >= 3";
        $players3 = $dbh->getOne($sql . $where);
        // 结果
        $data['moneyAll'] = $data['moneyAll'] ? $data['moneyAll'] / 100 : '0';
        $data['players2'] = $players2;
        $data['players3'] = $players3;
        if(!$data['moneyGameAll']) $data['moneyGameAll'] = '0';
        return $data;
    }

    public static function sumToTime($platformId, $serverId, $time){
        $sids = self::getMergeSids($platformId, $serverId);
        $dbh = Db::getInstance();
        $sql = 'select sum(money) as moneyAll, sum(moneyGame) as moneyGameAll, ';
        $sql .= 'count(account) as orders, count(distinct account) as players ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime <= '$time'";
        $data = $dbh->getRow($sql . $where);
        // 获得2次付费用户数
        $sql = 'select count(distinct account) as players2 ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime <= '$time' and isFirst = 0";
        $players2 = $dbh->getOne($sql . $where);
        // 获得3次付费用户数
        $sql = 'select count(distinct account) as players3 ';
        $sql .= 'from game.log_charge_order ';
        $where = "where platformId = '$platformId' and serverId in ($sids) ";
        $where .= "and ctime <= '$time' and chargeNth >= 3";
        $players3 = $dbh->getOne($sql . $where);
        // 结果
        $data['moneyAll'] = $data['moneyAll'] ? $data['moneyAll'] / 100 : '0';
        $data['players2'] = $players2;
        $data['players3'] = $players3;
        if(!$data['moneyGameAll']) $data['moneyGameAll'] = '0';
        return $data;
    }

    public static function getMergeSids($pid, $sid) {
        $tgsid2lgsids = Config::getInstance($pid)->get('tgsid2lgsids');
        $msid = "";
        if(isset($tgsid2lgsids[$sid])){
            $msid = Config::getInstance($pid.'_s'.$sid)->get('merge_server_id');
        }
        if($msid){
            $sid = $sid . ',' . $msid;
        }
        return $sid;
    }

}
