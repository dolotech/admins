<?php
/*-----------------------------------------------------+
 * 工具类
 *
 * @author erlang6@qq.com
 +-----------------------------------------------------*/

class Utils {

    /**
     * 检查keys
     * @param array $keys_arr
     * @param array $data_arr
     * @return bool 
     */
    public static function check_keys($keys_arr, $data_arr){
        foreach($keys_arr as $k){
            if(!array_key_exists($k, $data_arr) || $data_arr[$k] == '') {
                return $k;
            }
        }
        return true;
    }

    /**
     * 生成数据分页索引
     * @param string $url 显示页面的Url
     * @param int $totalRecord 总记录数
     * @param int $currentPage 当前页
     * @param int $limit 每页显示的记录数
     * @param int $half 往左右廷伸的索引个数,默认为5个
     * @return string html字串
     */
    public static function pager($url, $totalRecord, $currentPage, $limit, $half = 5) {
        $url = preg_replace ( '!&page=\d+!', '', $url );
        if($_REQUEST['limit']>0)
        {
            $url = preg_replace('/&limit=\d+/', "&limit={$_REQUEST['limit']}", $url);
        }
        $totalPage = $limit > $totalRecord ? 1 : $totalRecord % $limit ? ( int ) ($totalRecord / $limit) + 1 : ( int ) ($totalRecord / $limit);
        $currentPage = ($currentPage > 0 && $currentPage < $totalPage) ? ( int ) $currentPage : 0;
        $html = ($currentPage > 0) ? " <li><a href='$url&page=" . ($currentPage - 1) . "' title='上一页'>&laquo;</a></li>" : " <span class='d'>&laquo;</span>";
        if ($totalPage > $half * 2 && ($currentPage > $half)) {
            if (($currentPage + $half) < $totalPage) {
                $j = $currentPage + $half + 1;
                $i = $currentPage - $half;
            } else {
                $j = $totalPage;
                $i = $currentPage - ($half * 2 - ($j - $currentPage));
            }
        } else {
            $i = 0;
            $j = $totalPage > $half * 2 + 1 ? $half * 2 + 1 : $totalPage;
        }
        for(; $i < $j; $i ++) {
            $html .= ($i == $currentPage) ? " <li class='am-active'><a href='#'>" . ($i + 1) . "</a></li>" : " <li><a href='$url&page=$i'>" . ($i + 1) . "</a></li>";
        }
        $html .= ($currentPage + 1 < $totalPage) ? " <li><a href='$url&page=" . ($currentPage + 1) . "' title='下一页'>&raquo;</a></li>" : " <li class='am-disabled'><a href='#'>&raquo;</a></li>";
        $jump = ($half * 2 + 1) < $totalPage ? " <li><a href='javascript:void(0)' onclick=\"var page=prompt('请输入你想跳转到的页数(1-{$totalPage}):','');if(page>0 && page<=$totalPage){window.location.replace('$url'+'&page='+(page-1));}\" title='快速跳转页面'>&raquo;&raquo;</a></li>" : '';
        return $html . $jump . ' <li class="t">' . ($currentPage + 1) . "/$totalPage 共{$totalRecord}条记录</li>";
    }
    /**
     * 查IP所在地(需纯真IP数据包)
     * 来自discuz
     */
    public static function ip2addr($ip) {
    	if(!$ip)return '';
        //IP数据文件路径
        $dat_path = SYS_DIR . '/wry.dat';

        //检查IP地址
        if (! preg_match ( "/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip )) {
            return 'IP Address Error';
        }
        //打开IP数据文件
        if (! $fd = @fopen ( $dat_path, 'rb' )) {
            return 'IP date file not exists or access denied';
        }

        //分解IP进行运算，得出整形数
        $ip = explode ( '.', $ip );
        $ipNum = $ip [0] * 16777216 + $ip [1] * 65536 + $ip [2] * 256 + $ip [3];

        //获取IP数据索引开始和结束位置
        $DataBegin = fread ( $fd, 4 );
        $DataEnd = fread ( $fd, 4 );
        $ipbegin = implode ( '', unpack ( 'L', $DataBegin ) );
        if ($ipbegin < 0)
            $ipbegin += pow ( 2, 32 );
        $ipend = implode ( '', unpack ( 'L', $DataEnd ) );
        if ($ipend < 0)
            $ipend += pow ( 2, 32 );
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

        $BeginNum = 0;
        $EndNum = $ipAllNum;

        $ip1num = 0;
        $ip2num = 0;
        $ipAddr1='';
        $ipAddr2='';
        //使用二分查找法从索引记录中搜索匹配的IP记录
        while ( $ip1num > $ipNum || $ip2num < $ipNum ) {
            $Middle = intval ( ($EndNum + $BeginNum) / 2 );

            //偏移指针到索引位置读取4个字节
            fseek ( $fd, $ipbegin + 7 * $Middle );
            $ipData1 = fread ( $fd, 4 );
            if (strlen ( $ipData1 ) < 4) {
                fclose ( $fd );
                return 'System Error';
            }
            //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
            $ip1num = implode ( '', unpack ( 'L', $ipData1 ) );
            if ($ip1num < 0)
                $ip1num += pow ( 2, 32 );

            //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }

            //取完上一个索引后取下一个索引
            $DataSeek = fread ( $fd, 3 );
            if (strlen ( $DataSeek ) < 3) {
                fclose ( $fd );
                return 'System Error';
            }
            $DataSeek = implode ( '', unpack ( 'L', $DataSeek . chr ( 0 ) ) );
            fseek ( $fd, $DataSeek );
            $ipData2 = fread ( $fd, 4 );
            if (strlen ( $ipData2 ) < 4) {
                fclose ( $fd );
                return 'System Error';
            }
            $ip2num = implode ( '', unpack ( 'L', $ipData2 ) );
            if ($ip2num < 0)
                $ip2num += pow ( 2, 32 );

            //没找到提示未知
            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose ( $fd );
                    return 'Unknown';
                }
                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread ( $fd, 1 );
        if ($ipFlag == chr ( 1 )) {
            $ipSeek = fread ( $fd, 3 );
            if (strlen ( $ipSeek ) < 3) {
                fclose ( $fd );
                return 'System Error';
            }
            $ipSeek = implode ( '', unpack ( 'L', $ipSeek . chr ( 0 ) ) );
            fseek ( $fd, $ipSeek );
            $ipFlag = fread ( $fd, 1 );
        }

        if ($ipFlag == chr ( 2 )) {
            $AddrSeek = fread ( $fd, 3 );
            if (strlen ( $AddrSeek ) < 3) {
                fclose ( $fd );
                return 'System Error';
            }
            $ipFlag = fread ( $fd, 1 );
            if ($ipFlag == chr ( 2 )) {
                $AddrSeek2 = fread ( $fd, 3 );
                if (strlen ( $AddrSeek2 ) < 3) {
                    fclose ( $fd );
                    return 'System Error';
                }
                $AddrSeek2 = implode ( '', unpack ( 'L', $AddrSeek2 . chr ( 0 ) ) );
                fseek ( $fd, $AddrSeek2 );
            } else {
                fseek ( $fd, - 1, SEEK_CUR );
            }

            while ( ($char = fread ( $fd, 1 )) != chr ( 0 ) )
                $ipAddr2 .= $char;

            $AddrSeek = implode ( '', unpack ( 'L', $AddrSeek . chr ( 0 ) ) );
            fseek ( $fd, $AddrSeek );

            while ( ($char = fread ( $fd, 1 )) != chr ( 0 ) )
                $ipAddr1 .= $char;
        } else {
            fseek ( $fd, - 1, SEEK_CUR );
            while ( ($char = fread ( $fd, 1 )) != chr ( 0 ) )
                $ipAddr1 .= $char;

            $ipFlag = fread ( $fd, 1 );
            if ($ipFlag == chr ( 2 )) {
                $AddrSeek2 = fread ( $fd, 3 );
                if (strlen ( $AddrSeek2 ) < 3) {
                    fclose ( $fd );
                    return 'System Error';
                }
                $AddrSeek2 = implode ( '', unpack ( 'L', $AddrSeek2 . chr ( 0 ) ) );
                fseek ( $fd, $AddrSeek2 );
            } else {
                fseek ( $fd, - 1, SEEK_CUR );
            }
            while ( ($char = fread ( $fd, 1 )) != chr ( 0 ) ) {
                $ipAddr2 .= $char;
            }
        }
        fclose ( $fd );

        //最后做相应的替换操作后返回结果
        if (preg_match ( '/http/i', $ipAddr2 )) {
            $ipAddr2 = '';
        }
        $ipaddr = "$ipAddr1 $ipAddr2";
        $ipaddr = preg_replace ( '/CZ88.Net/is', '', $ipaddr );
        $ipaddr = preg_replace ( '/^s*/is', '', $ipaddr );
        $ipaddr = preg_replace ( '/s*$/is', '', $ipaddr );
        if (preg_match ( '/http/i', $ipaddr ) || $ipaddr == '') {
            $ipaddr = 'Unknown';
        }

        //转成utf8
        return mb_convert_encoding ( $ipaddr, "utf-8", "gbk" );
    }
}
