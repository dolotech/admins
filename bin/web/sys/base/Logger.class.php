<?php
/*-----------------------------------------------------+
 * 系统日志记录器
 * @author erlang6@qq.com
 +-----------------------------------------------------*/
class Logger{
    /**
     * 记录日志
     *
     * @param string $msg 日志内容
     * @param int $priority 级别
     */
    private static function log($msg, $priority){
        $data= array(
            'priority' => $priority,
            'msg' => $msg,
            'ip' => clientIp(),
            'ts' => time(),
        );
        $sql = Db::getInsertSql('log_system', $data);
        Db::getInstance()->exec($sql);
    }

    /**
     * 记录普通信息
     * @param string $msg 内容
     */
    public static function info($msg){
        self::log($msg, 0);
    }

    /**
     * 记录警告信息
     * @param string $msg 内容
     */
    public static function warning($msg){
        self::log($msg, 1);
    }

    /**
     * 记录错误信息
     * @param string $msg 内容
     */
    public static function error($msg){
        self::log($msg, 2);
    }

    public static function i($content, $writeRequestInfo = false){
        if($writeRequestInfo){
            $requestInformation = $_SERVER['REMOTE_ADDR'].', '.$_SERVER['HTTP_USER_AGENT'].', http://'.$_SERVER['HTTP_HOST'].htmlentities($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING']."\n";
        }else{
            $requestInformation = '';
        }
        $file = @fopen('./log/log_'.$_REQUEST['mod'].'_'.$_REQUEST['act'].'.txt',"a+");
        @fwrite($file, '['.date("Y-m-d H:i:s")."] " . $requestInformation . $content . "\n");  
        @fclose($file); 
    }
}
