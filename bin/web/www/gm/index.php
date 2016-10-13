<?php
/*-----------------------------------------------------+
 * 程序入口
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

//当前应用标识
define('APP_ID', 'gm');

//配置目录
define('CFG_DIR', '../../cfg');

//使用gz_handler压缩输出页面
define('USE_GZ_HANDLER', false);

//权限标识常量定义
define('ACT_NEED_AUTH', 1); //需要登录并验证
define('ACT_NEED_LOGIN', 1); //需要登录
define('ACT_OPEN', 2); //完全开放

include '../../sys/boot.php';

Admin::run();
