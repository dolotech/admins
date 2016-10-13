<?php
/*-----------------------------------------------------+
 * @author Rolong@vip.qq.com
 +-----------------------------------------------------*/
 
class Entry
{

	/**
	 * 运行
	 */
	public static function run()
	{
		
		if (USE_GZ_HANDLER)
		{
			ob_start('ob_gzhandler');
		} else
		{
			ob_start();
		}
		try
		{
			$actionFile = self::getActionFile();
            if (!file_exists($actionFile))
            {
				throw new NotifyException("无效的请求" );
            }
			include $actionFile;
			$actionName = 'Act_' . CURRENT_ACTION;
			
			if (! class_exists($actionName, false))
			{
				throw new NotifyException("无效的({$actionName})" );
			}
			$action = new $actionName();
			if (! ($action instanceof Action))
			{
				throw new NotifyException('"' . CURRENT_ACTION . '"无效');
			}
			if (! method_exists($action, 'process'))
			{
				throw new NotifyException('没有执行入口');
			}
			$action->process();
		} catch (NotifyException $e)
		{
			$e->raiseMsg();
		}
		ob_end_flush();
	}

	/**
	 * 返回用户请求指向的Action文件
	 */
	public static function getActionFile()
	{
		$m = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : 'default';
		$a = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'index';
		define('CURRENT_MODULE', $m);
		define('CURRENT_ACTION', $a);
		define('APP_ROOT', APP_DIR . '/' . APP_ID . '/' . $m);
		return APP_ROOT . '/' . $a . '.act.php';
	}

}
