<?php
/*-----------------------------------------------------+
 * 程序入口
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

//当前应用标识
define('APP_ID', 'api'); 

//配置目录
// define('CFG_DIR', '/data/wwwroot/game_config');
define('CFG_DIR', '../../cfg');

//使用gz_handler压缩输出页面
define('USE_GZ_HANDLER', false);

include '../../sys/boot.php';

Game::run();
