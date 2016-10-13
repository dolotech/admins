<?php
/*-----------------------------------------------------+
 * 公共函数
 * @author erlang6@qq.com
 +-----------------------------------------------------*/

/**
 * 打印指定变量的内容(用于调试)
 * @param mixd $var 变量名
 * @param string $label 标签名
 */
function dump($var, $label = null){
    $content = $label ? "<hr /><strong>$label :</strong>" : '';
    $content .= '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre><hr />';
    echo $content;
}

function d($var){
    $content = '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre>';
    echo $content;
    exit;
}

function dump2txt($filename, $var){
	$content = '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre>';
	file_put_contents($filename, $content);
}


function parse_query($var)
 {
  /**
   *  Use this function to parse out the query array element from
   *  the output of parse_url().
   */
  $var  = parse_url($var, PHP_URL_QUERY);
  $var  = html_entity_decode($var);
  $var  = explode('&', $var);
  $arr  = array();

  foreach($var as $val)
   {
    $x          = explode('=', $val);
    $arr[$x[0]] = $x[1];
   }
  unset($val, $x, $var);
  return $arr;
 }

/**
 * 用addslashes处理变量,可处理多维数组（使用反斜线引用字符串）
 * @param mixed $vars 待处理的数据
 * @return mixed
 */
function addQuotes($vars) {
    return is_array ( $vars ) ? array_map ( __FUNCTION__, $vars ) : addslashes ( $vars );
}

/**
 * 对指定变量进行stripslashes处理,可处理多维数组（去掉字符串中的反斜线字符。若是连续二个反斜线，则去掉一个，留下一个。若只有一个反斜线，就直接去掉）
 * @param mixed $vars 待处理的数据
 * @return mixed
 */
function stripQuotes($vars) {
    return is_array ( $vars ) ? array_map ( __FUNCTION__, $vars ) : stripslashes ( $vars );
}

/**
 * 对变量进行 trim 处理,支持多维数组.(截去字符串首尾的空格)
 * @param mixed $vars
 * @return mixed 
 */
function trimArr($vars) {
    return is_array ( $vars ) ? array_map ( __FUNCTION__, $vars ) : trim ( $vars );
}

/**
 * 对变量进行 nl2br 和 htmlspecialchars 操作,支持多维数组.（可将字符串中的换行符转成HTML的换行符号）
 * @param mixed $vars
 * @return mixed  
 */
function textFormat($vars) {
    return is_array ( $vars ) ? array_map ( __FUNCTION__, $vars ) : nl2br ( htmlspecialchars ( $vars ) );
}

/**
 * 将Y-m-d H:i:s格式的时间转成unixtime
 * @param string $data 日期
 * @param string $time 时间
 * @return int
 */
function unixtime($date, $time = null) {
    $date = explode ( '-', $date );
    if ($time) {
        list ( $h, $i, $s ) = explode ( ':', $time );
        return mktime ( $h, $i, $s, $date [1], $date [2], $date [0] );
    }
    return mktime ( 0, 0, 0, $date [1], $date [2], 0 + $date [0] );
}

/**
 * 判断某个字串是否Y-m-d格式的时间字串
 * @param string $date 日期
 * @return bool
 */
function isDate($date){
    $dPat	='([1-9])|((0[1-9])|([1-2][0-9])|(3[0-2]))';
    $mPat	='([1-9])|((0[1-9])|(1[0-2]))';
    $yPat	='(19|20)[0-9]{2}';
    $pattern="!^($yPat)-($mPat)-($dPat)$!";
    return preg_match($pattern, $date);
}

/**
 * 产生一个随机字串
 * @param int $len 指定随机字串的长度
 * @param string $scope 随机字符的取值范围
 * @return string
 */
function randString($len, $scope = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890") {
    $strLen = strlen($scope) - 1;
    $string = '';
    for($i = 0; $i < $len; $i ++) {
        $string .= substr($scope, mt_rand( 0, $strLen ), 1);
    }
    return $string;
}

/**
 * 得到客户端IP地址 
 * @return string
 */
function clientIp(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}

/**
 * 字串长度计算(可计算utf8)
 * @param string $str 待计算的字串
 * @return int 长度
 */
function utf8strlen($str) {
    $i = 0;
    $count = 0;
    $len = strlen ( $str );
    while ( $i < $len ) {
        $chr = ord ( $str [$i] );
        $count ++;
        $i ++;
        if ($i >= $len) break;
        if ($chr & 0x80) {
            $chr <<= 1;
            while ( $chr & 0x80 ) {
                $i ++;
                $chr <<= 1;
            }
        }
    }
    return $count;
}

/*
 * 截取中文字符
 * @param string $str 字串
 * @param int $len 截取长度
 * @return string 截取后的字串
 */
function cnSubstr($str, $len) {
    for($i = 0; $i < $len; $i ++) {
        $temp_str = substr ( $str, 0, 1 );
        if (ord ( $temp_str ) > 127) {
            $i ++;
            if ($i < $len) {
                $new_str [] = substr ( $str, 0, 3 );
                $str = substr ( $str, 3 );
            }
        } else {
            $new_str [] = substr ( $str, 0, 1 );
            $str = substr ( $str, 1 );
        }
    }
    return join($new_str);
}

/**
 * 写文件系统
 * @param string $file 文件名
 * @param string $content 内容
 */
function writeFile($file, $content, $mode='wb'){
    $oldMask= umask(0);
    $fp= @fopen($file, $mode);
    fwrite($fp, $content);
    fclose($fp);
    umask($oldMask);
}

/**
 * 创建帐号关联的Ticket
 * @param int $accId 帐号ID
 * @param string $accName 帐号名称
 * @param string $serverId 服务器ID
 * @param int $ts 主服务器端ticket时间戳
 * @param string $key 附加加密串
 */
/*
function createTicket($accId, $accName, $ts, $serverId, $key){
    return md5($serverId . $key . $accId . $accName . $ts);

}
 */

/**
 * 创建Ticket
 * @param int $accId 帐号ID
 * @param string $accName 帐号名称
 * @param int $ts 主服务器端ticket时间戳
 * @param string $key 附加加密串
 */
function createTicket($accId, $accName, $ts, $key){
    return md5($accId . $accName . $ts . $key);
}

/**
 * 创建角色关联的Ticket
 * @param int $roleId 角色ID
 * @param string $serverId 服务器ID
 * @param int $ts 主服务器端ticket时间戳
 * @param string $key 加密串
 */
function createRoleTicket($roleId, $serverId, $ts, $key){
    return md5($key . $roleId . $ts . md5($serverId));
}

/**
 * 锁定指定文件请求,防止短时间内发送大量重复请求
 * @param int $sec 文件锁的时间超时设定，当超出设定时间而没有手动解锁时会自动解锁(默认10秒后锁失效)
 */
function lockRequest($sec=10){
    $key = 'system_request_lock_'.$_SESSION['role_id'].'_'.md5($_SERVER["SCRIPT_NAME"]);
    $mc = MemoryCache::getInstance();
    $data = $mc->fetch($key);
    if(false == $data){
        $mc->set($key, true, false, $sec);
    }else{
        throw new RequestLockException('该请求已被锁定，请稍候再访问.');
    }
    register_shutdown_function('releaseRequestLock', $key);
}

/**
 * 解除Request锁定
 * @param string $key 锁ID
 */
function releaseRequestLock($key){
    MemoryCache::getInstance()->delete($key);
}
/**
 * 将秒转换成可阅读的时间格式
 * @author Tim <mianyangone@gmail.com> 
 * @param int $second 秒数
 * @param string $format 可阅读的构成类型(Y|W|D|H|M)
 * @return string;
 */
function convertTime($second,$format = 'Y'){
    if ($second <= 0 ) return 0;	

    $sM = 60; //60秒
    $sH = $sM * 60; //60 分钟
    $sD = $sH * 24; //24小时
    $sW = $sD * 7; //7天
    $sY = $sD * 365; // 365天

    $string = '';
    $format = strtoupper($format);
    switch ($format){
    case 'Y':
        $y = (int)($second/$sY);
        $second -= $y * $sY;
        if ($y>0) $string .= "$y 年 "; 
    case 'W':
        $w = (int)($second/$sW);
        $second -= $sW * $w;
        if ($w>0) $string .= "$w 周 ";
    case 'D':
        $d = (int)($second/$sD);
        $second -= $sD * $d;
        if ($d>0) $string .= "$d 天 ";
    case 'H':
        $h = (int)($second/$sH);
        $second -= $sH * $h;
        if ($h>0) $string .= "$h 小时 ";
    case 'M':
        $m = (int)($second/$sM);
        $second -= $sM * $m;
        if ($m>0) $string .= "$m 分  ";
        break;
    default:
        convertTime($second,'Y');
    }
    return $string;
}

function secondToString($diff)
{
  if($diff < 60) {
    return intval($diff % 60) . "秒";
  } elseif($diff < 60 * 15) {
    return intval($diff / 60) . "分钟";
  } elseif($diff < 60 * 30) {
    return "一刻钟";
  } elseif($diff < 60 * 60) {
    return "半小时";
  } elseif($diff < 60 * 60 * 24) {
    return intval($diff / 60 / 60) . "小时";
  } elseif($diff < 60 * 60 * 24 * 7) {
    return intval($diff / 60 / 60/ 24) . "天";
  } elseif($diff < 60 * 60 * 24 * 30) {
    return intval($diff / 60 / 60/ 24/7) . "星期";
  } elseif($diff < 60 * 60 * 24 * 365) {
    return intval($diff / 60 / 60/ 24 / 30) . "月";
  } else {
    return intval($diff / 60 / 60/ 24 / 365) . "年";
  }
}


/**
 * The main function for converting to an XML document.
 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
 *
 * @param array $data
 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
 * @param SimpleXMLElement $xml - should only be used recursively
 * @return string XML
 */
function arrayToXml($arr, $rootNodeName='root', $xml=null){
    // turn off compatibility mode as simple xml throws a wobbly if you don't.
    if (ini_get('zend.ze1_compatibility_mode') == 1){
        ini_set ('zend.ze1_compatibility_mode', 0);
    }

    if ($xml == null){
        $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
    }

    foreach($arr as $k=> $v){
        if (is_numeric($k)){
            // make string key...
            $k= "row_". (string) $k;
        }

        // replace anything not alpha numeric
        //$k= preg_replace('/[^a-z]/i', '', $k);

        // if there is another array found recrusively call this function
        if (is_array($v)){
            $node = $xml->addChild($k);
            arrayToXml($v, $rootNodeName, $node);
        } else {
            // add single node.
            //$v= htmlentities($v);
            $xml->addChild($k, $v);
        }
    }
    // pass back as string. or simple xml object if you want!
    return $xml->asXML();
}

/**
 * 把数组转成HTML的table
 * @param $ay
 * @return string
 */
function arrayToTable($ay, $flag=0, $style=true){
    if(!is_array($ay)) return "Not array<br>\n";
    if(count($ay)==0)  return "<div style='text-align:center'> - - - </div>";
    
    $t = "";
	if($style){
		$t .= "<style>";
		$t .= ".func_array_to_table{border:1px #333 solid; border-right:none; border-bottom:none; padding:0}";
		$t .= ".func_array_to_table tr{vertical-align: top}";
		$t .= ".func_array_to_table td{border:1px #333 solid; border-left:none; border-top:none; padding:2px}";
		$t .= "</style>";
	}
    $t .= "<p>";
    if($flag){
        $t .="<table class='func_array_to_table' bgcolor=#eefbde cellspacing=0 cellpadding=0>";
        $t .= "<tr>";
        foreach($ay as $k=>$a){
            $t .= "<td><b>$k</b></td>";
        }
        $t .= "</tr>";

        $t .= "<tr>";
        foreach($ay as $a){
            $td = $a;
            if(is_array($a)){
                $td = arrayToTable($a, 1-$flag, false);
            }else if(is_object($a)){
                $td = '<i><font color=blue>'.get_class($a).'</font></i>';
            }else if(!$a && !is_numeric($a)){
                $td = '&nbsp;';
            }

            $t .= "<td>$td</td>";
        }
        $t .= "</tr>\n";
        $t .= '</table>';
    }else{
        $t .= "<table class='func_array_to_table' bgcolor=#ffeedd cellspacing=0 cellpadding=0>";
        foreach($ay as $k=>$a){
            $t .= "<tr>";
            $t .= "<td><b>$k</b></td>";
            $td = $a;
            if(is_array($a)){
                $td = arrayToTable($a, 1-$flag, false);
            }else if(is_object($a)){
                $td = '<i><font color=blue>'.get_class($a).'</font></i>';
                }else if(!$a && !is_numeric($a)){
                $td = '&nbsp;';
            }

            $t .= "<td>$td</td>";
            $t .= "</tr>\n";
        }
        $t .= '</table>';
    }
    $t .= '</p>';
    
    return $t;
}

/**
 * 把php的数组成erlang的tuple list的字符串形式
 * @param unknown_type $arr
 * @return unknown_type
 */
function arrayToTupleList($arr){
    $str = "[";
    $sp = '';
    foreach($arr as $k => $v){
        $str .= $sp;
        if(is_array($v)){
            $str .= arrayToTupleList($v);
        }else if(is_numeric($v)){
            $str .= '{' . stripcslashes($k) . ',' . $v . '}';
        }else{
            $str .= '{' . stripcslashes($k) . ',<<"' . stripcslashes($v) . '">>}';
        }
        $sp = ',';
    }
    return $str . "]\n";
}

/**
 * 用于开发的时候打印异常信息的
 * @param $e object Exception对象
 * @return unknown_type
 */
function pocketException($e){
    $html = "";
    $html.= "<p>";
    $html.= "<h4>".$e->getMessage()."<h4>";
    $html.= "<h5>".$e->getFile()."【".$e->getLine()."】</h5>";
    $html.= $e->getTraceAsString();
    $html.= "</p>";
    
    echo $html;
}

function becomeDaemon($uid, $gid) {
    if(!posix_setgid($gid)) {
        print "Unable to setgid to $gid!\n";
        exit;
    }    
    if(!posix_setuid($uid)) {
        print "Unable to setuid to $uid!\n";
        exit;
    }
    $pid = pcntl_fork();
    if($pid) {
        exit; //退出父进程
    }
    posix_setsid();
    chdir('/');
    umask(0); //清除umask
    return posix_getpid();
}

function get_server_id(){
    $config = Config::getInstance();
    //不是合服的，直接从配置文件读取
    if( !$config->get('union_server') ){
        return $config->get('server_id');
    }
    //通过GET参数传入server_id
    if( $_GET['server_id'] ){
        $cfg_server_id = (array)$config->get('server_id');
        if( in_array($_GET['server_id'], $cfg_server_id) ){
            return $_GET['server_id'];
        }
    }
    //根据URL获取server_id
    $server_name = (!empty($_SERVER['HTTP_HOST'])) 
        ? strtolower($_SERVER['HTTP_HOST']) 
        : ((!empty($_SERVER['SERVER_NAME'])) 
            ? $_SERVER['SERVER_NAME'] 
            : getenv("SERVER_NAME")); 
    $domains = explode(".", $server_name);
    $server_id = count($domains)>1 
        ? ($n = preg_replace("/^.*[^\\d]+([0-9]+)$/", "$1", $domains[0]))==$domains[0] ?  0 : $n
        : 0;
    if($server_id){
        $cfg_server_id = (array)$config->get('server_id');
        if( in_array($server_id, $cfg_server_id) )
            return $server_id;
        else
            return 0;
    }
    //合服的，直接从配置文件读取第1个server_id
    $cfg_server_id = (array)$config->get('server_id');
    return $cfg_server_id[0];
}

function requesttostring(){
	if(!$_REQUEST){
		return '';
	}
	foreach($_REQUEST as $k=>$v){
		$a[$k] = $k.'='.$v;
	}
	return implode('&', $a);
}

function file_type($filename)  
{  
    $file = fopen($filename, "rb");  
    $bin = fread($file, 2); //只读2字节  
    fclose($file);  
    $strInfo = @unpack("C2chars", $bin);  
    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);  
    $fileType = '';  
    switch ($typeCode)  
    {  
        case 7790:  
            $fileType = 'exe';  
            break;  
        case 7784:  
            $fileType = 'midi';  
            break;  
        case 8297:  
            $fileType = 'rar';  
            break;          
        case 8075:  
            $fileType = 'zip';  
            break;  
        case 255216:  
            $fileType = 'jpg';  
            break;  
        case 7173:  
            $fileType = 'gif';  
            break;  
        case 6677:  
            $fileType = 'bmp';  
            break;  
        case 13780:  
            $fileType = 'png';  
            break;  
        default:  
            $fileType = 'unknown: '.$typeCode;  
    }  
  
    //Fix  
    if ($strInfo['chars1']=='-1' AND $strInfo['chars2']=='-40' ) return 'jpg';  
    if ($strInfo['chars1']=='-119' AND $strInfo['chars2']=='80' ) return 'png';  
  
    return $fileType;  
} 

// protocol

// function fix_client_field($v){
//     switch($v){
//     case 'int32': 
//         $v = 'int';
//         break;
//     case 'string': 
//         $v = 'String';
//         break;
//     default: 
//         break;
//     }
//     return $v;
// }
// 
// function parse_client_field($data, $ss, $index, $title){
//     $result = $title;
//     foreach($data as $k => $v){
//         $index = $index - 1;
//         if(is_array($v)){
//             if(count($v) == 1 && in_array('int32', $v)){
//                 $result .= $k . " Array<int32>\r\n";
//             }else{
//                 if(array_key_exists('_obj_name', $v)){
//                     $obj_name = $v['_obj_name'];
//                 }else{
//                     $obj_name = ucfirst($k) . 'Vo';
//                 }
//                 $title1 = 'OBJ_' . $obj_name . "\r\n";
//                 $result .= $k . " Array<" . $obj_name . ">\r\n";
//                 $ss[] = parse_client_field($v, array(), $index, $title1);
//             }
//         }else if($k != '_obj_name'){
//             $result .= $k . " " . fix_client_field($v) . "\r\n";
//         }
//     }
//     $ss[] = $result;
//     return $ss;
// }
// 
// function array_flatten($a){ //flattens multi-dim arrays (distroys keys)
//     $ab = array(); if(!is_array($a)) return $ab;
//     foreach($a as $value){
//         if(is_array($value)){
//             $ab = array_merge($ab,array_flatten($value));
//         }else{
//             array_push($ab,$value);
//         }
//     }
//     return $ab;
// }
// 
// function parse_client_protocol($protocol_id, $protocol_val){
//     //echo "<br/>protocol_id:" . $protocol_id;
//     $pid = "protocol_" . $protocol_id . "\r\n";
//     $title = $pid . "CCMD_" . strtoupper($protocol_val['name']) . "\r\n";
//     $c2s = array();
//     $c2s = parse_client_field($protocol_val['c2s'], $c2s, 100, $title);
// 
//     //$title = "S2C_" . $protocol_id . "\r\n";
//     $title = "SCMD_" . strtoupper($protocol_val['name']) . "\r\n";
//     $s2c = array();
//     $s2c = parse_client_field($protocol_val['s2c'], $s2c, 100, $title);
//     sort($c2s);
//     sort($s2c);
//     $ss = array_merge(array_flatten($c2s), array_flatten($s2c));
//     $str = join("\r\n", $ss);
//     //var_dump($str);
//     return $str;
// }
// 

// <<?KVS_ZIP_VERSION:8, Power:16, SignDays:8, SignTime:32, SignOldDays:8>>.

// format 参数的可能值：
// a - NUL-padded string
// A - SPACE-padded string
// h - Hex string, low nibble first
// H - Hex string, high nibble first
// c - signed char
// C - unsigned char
// s - signed short (always 16 bit, machine byte order)
// S - unsigned short (always 16 bit, machine byte order)
// n - unsigned short (always 16 bit, big endian byte order)
// v - unsigned short (always 16 bit, little endian byte order)
// i - signed integer (machine dependent size and byte order)
// I - unsigned integer (machine dependent size and byte order)
// l - signed long (always 32 bit, machine byte order)
// L - unsigned long (always 32 bit, machine byte order)
// N - unsigned long (always 32 bit, big endian byte order)
// V - unsigned long (always 32 bit, little endian byte order)
// f - float (machine dependent size and representation)
// d - double (machine dependent size and representation)
// x - NUL byte
// X - Back up one byte
// @ - NUL-fill to absolute position
function unpack_kvs($bin){
    $bytes = unpack('C1version/A*data', $bin);
    switch($bytes['version']){
    case 1 :
        return unpack('n1power', $bytes['data']);
        break;
    case 2 :
        return unpack('n1power/C1sign_days/N1sign_time/C1sign_old_days', $bytes['data']);
        break;
    }
}

function request_uri()
{
    if (isset($_SERVER['REQUEST_URI']))
    {
        $uri = $_SERVER['REQUEST_URI'];
    }
    else
    {
        if (isset($_SERVER['argv']))
        {
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        }
        else
        {
            $uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}

function formatSeconds($seconds){
    $hours = intval($seconds/3600); 
    if($hours < 10) $hours = '0'.$hours;
    $remain = $seconds%3600; 
    $mins = intval($remain/60); 
    if($mins < 10) $mins = '0'.$mins;
    $secs = $remain%60; 
    if($secs < 10) $secs = '0'.$secs;
    return "$hours:$mins:$secs";
}

function today0clock(){
    return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
}
