<?php

/**
 * 统计上报接口调用情况
 *
 * @version 3.0.2
 * @author open.qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 * @ History:
 *               3.0.2 | sparkeli | 2012-03-06 15:33:04 | initialize statistic fuction which can report API's access time and number to background server
 */


class SnsStat
{
	/**
	 * 执行一个 统计上报
	 *
	 * @param string $stat_url 统计上报的URL
	 * @param float $start_time 统计开始时间
	 * @param array $params 统计参数数组
	 * @return 
	 */
	static public function statReport($stat_url, $start_time, $params)
	{   
		$end_time = self::getTime();
		$params['time'] = round($end_time - $start_time, 4);
		$params['timestamp'] = time();
		$params['collect_point'] = 'sdk-php-v3';
		$stat_str = json_encode($params);
		//发送上报信息
		$host_ip = gethostbyname($stat_url);
		if ($host_ip != $stat_url)
		{
			$sock = socket_create(AF_INET, SOCK_DGRAM, 0);
			if (false === $sock)
			{
				return;
			}
			socket_sendto($sock, $stat_str, strlen($stat_str), 0, $host_ip, 19888);
			socket_close($sock);	
		}
	}
	
	static public function getTime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

// end of script
