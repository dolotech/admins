<?php
/***************************************************************
 * 自定义错误处理
 * 
 * @author erlang6@qq.com
 ***************************************************************/
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

function errorHandler($errno, $errstr) {
    $errRpt= error_reporting();
    if (($errno & $errRpt) != $errno) return;
    throw new ErrorException("PHP Error:[$errno] $errstr", $errno);
}

function exceptionHandler($e) {
    $msg= "Exception Message:\n[".$e->getCode().'] "'.$e->getMessage().'" in file '.$e->getFile()." (".$e->getLine().").\nDebug Trace:\n".$e->getTraceAsString()."\n\n";

    writeFile(VAR_DIR.'/error_log.txt', '['.date('Y-m-d H:i:s')."]\n".$msg, 'ab');
    //header('Content-Type: text/html; charset=utf-8');
    echo "<pre>$msg</pre>";
}
