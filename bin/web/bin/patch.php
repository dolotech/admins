<?php
/*-----------------------------------------------------+
 * 命令行下执行的PHP脚本,用于测试或补录数据
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

//配置目录
define('CFG_DIR', '/data/web/cfg');

include '/data/web/sys/boot.php';

$ps = Config::getInstance()->get('platformsServers');
foreach($ps as $pid => $servers){
    foreach($servers as $sid){
        for($i = 80; $i > 0; $i--){
            // 流失统计
            GameStat::calcStatLostLevelDay($pid, $sid, $i);
            // 留存统计
            // GameStat::calcStatRetentionDay($pid, $sid, $i);
            // GameStat::calcStatOnline($pid, $sid, $i);
            // GameStat::calcStatDay($pid, $sid, $i);
            echo '.';
            usleep(1000000);
        }
    }
}
echo 'ok';
