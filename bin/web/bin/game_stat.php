<?php
/*-----------------------------------------------------+
 * 每日统计
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

//配置目录
define('CFG_DIR', '/data/web/cfg');

include '/data/web/sys/boot.php';

GameStat::runPerDay();
