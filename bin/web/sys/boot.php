<?php
/*-----------------------------------------------------+
 * 系统启动文件
 * 在所有应用开始之前对运行环境作必要的初始化工作
 *
 * @author erlang6@qq.com
 +-----------------------------------------------------*/
 

//if(isset($_GET['XGE_DEBUG']) && $_GET['XGE_DEBUG'] == 'open')
//{
//    define('DEBUG', 1);
//}else{
//    define('DEBUG', !isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR']=='127.0.0.1'?1:0);
//}
//
//if(DEBUG)
//	error_reporting(E_ALL ^E_NOTICE);
//else
//	error_reporting(0);
	
error_reporting(E_ALL ^E_NOTICE);

$THIS_DIR = dirname(__FILE__);
include $THIS_DIR.'/global.php';
//include $THIS_DIR.'/errorhandler.php';

define('IDEL_TIMEOUT', 1800); //登录失效时长，单位：秒
define('TIMESTAMP', time());

/* TODO:memcache已经禁用
//系统Memcache配置
define('MC_HOST_SYSTEM', '127.0.0.1');
define('MC_PORT_SYSTEM', 11211);
define('MC_HOST_SESSION', '127.0.0.1');
define('MC_PORT_SESSION', 11211);
 */

//系统目录结构
define('ROOT',              str_replace('\\', '/', realpath($THIS_DIR.'/..')));
define('SYS_DIR',           ROOT.'/sys');
define('LIB_DIR',           ROOT.'/sys/lib');
define('APP_DIR',           ROOT.'/app');
define('VAR_DIR',           ROOT.'/var');
define('WEB_DIR',           ROOT.'/www');

set_include_path(SYS_DIR.'/base' .PATH_SEPARATOR. APP_DIR.'/module' .PATH_SEPARATOR. SYS_DIR.'/exception' .PATH_SEPARATOR. SYS_DIR.'/helper' .PATH_SEPARATOR. SYS_DIR.'/api/facebook' .PATH_SEPARATOR. APP_DIR.'/crontab' .PATH_SEPARATOR 
    . SYS_DIR . '/base/PHPExcel' . PATH_SEPARATOR
    . get_include_path());
spl_autoload_register('classLoader');

$config = Config::getInstance();
//一些常量定义
// define('SERVER_ID', $config->get('server_id'));
// define('SERVER_NAME', $config->get('server_name'));
// define('SERVER_KEY', $config->get('server_key'));
// define('SERVER_HOST', $config->get('server_host'));
// define('SESSION_TIMEOUT', $config->get('session_timeout'));
define('NPC_SLAVE_NUMBER', 2);
//时区设置
date_default_timezone_set($config->get('timezone'));

/* TODO:memcache已经禁用
//Session保存到memcache中
ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', 'tcp://'.MC_HOST_SESSION.':'.MC_PORT_SESSION);
*/
header('P3P: CP=CAO PSA OUR');
session_set_cookie_params(24 * 3600 * 3);
session_start();



//如果PHP没有自动转义Request数据则在这里进行转义处理
if (!get_magic_quotes_gpc()) {
    $_GET = addQuotes($_GET);
    $_POST = addQuotes($_POST);
    $_FILES= addQuotes($_FILES);
    $_COOKIE= addQuotes($_COOKIE);
}
//不处理cookie 防止与平台冲突
$GPCS = array($_GET, $_POST);
foreach($GPCS as $val)
{
    if($val && is_array($val))
    {
        foreach($val as $k=>$v)
        {
            $_REQUEST[$k] = $v;
        }
    }
}

/**
 * 类自动加载函数
 * @param string $class 类名
 */
function classLoader($class){
    if (class_exists($class) || interface_exists($class)) {
        return;
    }
    if(strpos($class, '_') > 0){
        include str_replace("_", "/", $class) . '.php';
        return;
    }
    include $class.'.class.php';
}
