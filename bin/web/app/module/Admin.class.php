<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jecelyin
 * Date: 11-3-28
 * Time: 上午11:46
 * 后台管理类
 */

class Admin
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
			if (ACT_OPEN != $action->AuthLevel)
			{
				if (!isset($_SESSION['admin_uid']) || !Db::getInstance()->getOne("select id from base_admin_user where id={$_SESSION['admin_uid']} and passwd='{$_SESSION['admin_passwd']}'"))
				{
					//是后台登录超时，要跳转
					$ref = $_SERVER['REQUEST_URI'];
					if(strpos($ref, 'mod=') < 1)
						$ref = '';
					//throw new NotifyException('你还没有登录，无法访问指定页面。请登录后再尝试访问。',
					// 401, array('登录' => Admin::url('login', 'default', array('ref'=>$ref))));
					Admin::redirect(Admin::url('login', 'default', array('ref'=>$ref)));
				}
				/*if (time() - $_SESSION['admin_last_action_time'] > SESSION_TIMEOUT)
				{
					throw new NotifyException('上次登录已经失效，请重新登录后再访问。', 401, array('登录' => Admin::url('login', 'default')));
				}*/
				$_SESSION['admin_last_action_time'] = time();
			}
			$action->process();
		} catch (NotifyException $e)
		{
			$e->raiseMsg();
		}
		/*
        catch(Exception $e){
            $code = $e->getCode() ? $e->getCode() : 500;
            header("HTTP/1.1 $code ".$e->getMessage());
            exit($e->getMessage());
        }
         */
		ob_end_flush();
	}

	/**
	 * 返回用户请求指向的Action文件
	 */
	public static function getActionFile()
	{
		$m = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : 'default';
		$a = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'index';
		$c = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : 0;
		if ($_SESSION['admin_group_id'] > 0)
			if (! self::hasPermission($m, $a, $c))
				throw new NotifyException("您没有访问该功能的权限！");
		define('CURRENT_MODULE', $m);
		define('CURRENT_ACTION', $a);
		define('APP_ROOT', APP_DIR . '/' . APP_ID . '/' . $m);
		return APP_ROOT . '/' . $a . '.act.php';
	}

	public static function getUserGroup()
	{
		$db = Db::getInstance();
		$data = $db->getAll("select * from  `base_admin_user_group`");
		$rt = array(0 => array('name' => '超级管理员'));
		foreach ($data as $dval)
		{
			$dval['menu'] = explode(',', $dval['menu']);
			$rt[$dval['id']] = $dval;
		}
		return $rt;
	}

	/**
	 * 权限菜单ID来判断是否有权限
	 * @param <int> $id 菜单ID
	 */
	public static function hasP($id)
	{
        //隐藏元宝与充值相关
        if($id == 10018 && $_SESSION['admin_group_id'] == 0)
            return false;
		$menu = self::getAdminMenu(true);
		foreach ($menu as $mk=> $mval)
		{
			foreach ($mval['sub'] as $sk=>$sub)
			{
				$key = $mk.$sk;
				if($key == $id)
					return true;
			}
		}
		return false;
	}

	public static function getTicket($ts)
	{
		$uid = $_SESSION['admin_uid'];
		$uname = $_SESSION['admin_username'];
		if(!$uid || !$uname)return '';
		$pwd = Db::getInstance()->getOne("select passwd from  `base_admin_user` where id = ".$uid);
		$key = Config::getInstance()->get('admin_key');
		return md5($key.$uname.$pwd.$ts);
	}

	/**
	 * 是否有权限访问该模块，一般不作调用，由自动加载时判断
	 * @param <string> $mod 模块
	 * @param <string> $act 动作
	 * @return true | false
	 */
	public static function hasPermission($mod, $act, $cmd = 0)
	{
		if (in_array($mod, array('default')))
			return true;
		$menu = self::getAdminMenu();
		foreach ($menu as $mval)
		{
			foreach ($mval['sub'] as $sub)
			{
				$info = parse_query($sub['url']);
				if (! $info['mod'])
					continue;
                if ($info['mod'] == $mod && $info['act'] == $act){
                    if($cmd > 0){
                        if($cmd == $info['cmd']) return true;
                    }else{
                        return true;
                    }
                }
			}
		}
		return false;
	}

	public static function getMenu()
	{
		$menu = require APP_DIR.'/menu.cfg.php';
		$rt = array();
		foreach ($menu as $mid => $val)
		{
			foreach ($val['sub'] as $sk => $sv)
				$rt[$mid . $sk] = $sv;
		}
		return $rt;
	}

	public static function getAdminMenu($append=false, $allshow=false)
	{
		$allMenu = require APP_DIR.'/menu.cfg.php';
		$gid = $_SESSION['admin_group_id'];
		//超级管理员
		if ($gid == 0 || $allshow)
			return $allMenu;
		$db = Db::getInstance();
		$menu = $db->getOne("select menu from base_admin_user_group where id = {$gid}");
		$menuArr = explode(',', $menu);
		foreach ($allMenu as $pid => &$pm)
		{
			foreach ($pm['sub'] as $sid => $sval)
			{
				$key = $pid . $sid;
				if (! in_array($key, $menuArr))
					unset($pm['sub'][$sid]);
			}
			if (! $pm['sub'])
				unset($allMenu[$pid]);
		}
		return $allMenu;
	}

	public static function getShowMenu()
	{
        $menus = self::getAdminMenu();
		foreach ($menus as $key => $menu)
		{
			foreach ($menu['sub'] as $k => $item)
			{
                if($item['hidden']){
                    unset($menu['sub'][$k]);
                    unset($menus[$key]['sub'][$k]);
                }
			}
			if (! $menu['sub'])
				unset($menus[$key]);
		}
		return $menus;
	}

	/**
	 * 生成一个影射到指定模块和动作的URL
	 *
	 * @param string $action 动作标识字串，空字串表示当前动作
	 * @param string $module 模块标识字串，空字串表示当前模块
	 * @param array $params 附加到url的参数
	 * @param bool $useParamCache 是否使用缓存中的Url参数(被保存在SESSION中)
	 * @return string $url URL字串
	 */
	public static function url($action = null, $module = null, $params = array(), $useParamCache = false)
	{
		/*
		if (is_array($params) && $useParamCache && isset($_SESSION['param_cache']) && is_array($_SESSION['param_cache']))
		{
			$params = array_merge($_SESSION['param_cache'], $params);
		} else
			if ($useParamCache && isset($_SESSION['param_cache']) && is_array($_SESSION['param_cache']))
			{
				$params = stripQuotes($_SESSION['param_cache']);
			}
		*/
		$action = $action ? $action : CURRENT_ACTION;
		$module = $module ? $module : CURRENT_MODULE;
		$url = array("?mod=$module&act=$action");
		//自动获得参数
		if(isset($_REQUEST['kw']) && is_array($_REQUEST['kw']))
		{
			foreach($_REQUEST['kw'] as $k=>$v)
				$params['kw['.$k.']'] = $v;
		}
		//为addParamCache函数打补丁
		if(isset($_REQUEST['ParamCache']) && is_array($_REQUEST['ParamCache']))
		{
			foreach($_REQUEST['ParamCache'] as $k=>$v)
				$params[$k] = $v;
		}
		if (is_array($params))
		{
			foreach ($params as $k => $v)
			{
				$url[] = urlencode($k) . '=' . urlencode($v);
			}
		}
		$url = implode($url, '&');
		return preg_match('!^' . $_SERVER['SCRIPT_NAME'] . '!', $_SERVER['REQUEST_URI']) ? $_SERVER['SCRIPT_NAME'] . $url : $url;
	}

	/**
	 * 扩展类似JS的alert函数，响应后直接退出php执行脚本
	 * @param $msg 提示信息
	 * @param $act 默认动作返回上一页，其它：href转到链接，close关闭当前窗口
	 * @param $href 网址
	 * @return null
	 */
	public static function alert($msg = '操作失败 :-(', $act = 'href', $href = '')
	{
		$js = '';
		switch ($act)
		{
			case 'href':
				if(!$href)$href = $_SERVER['HTTP_REFERER'];
				$js = "location.href='$href';";
				break;
			case 'close':
				$js = "window.open('','_parent','');window.close();";
				break;
			default:
				$js = "history.go(-1);";
		}
		//避免因字符编码问题
		echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<body><script type="text/javascript">
alert("' . $msg . '");' . $js . '
</script></body></html>';
		exit();
	}

	/**
	 * 页面重定向
	 * @param string $url url字符串
	 */
	public static function redirect($url)
	{
		header('Location:' . $url);
		exit();
	}

    public static function log($event, $memo='')
    {
        $db = Db::getInstance();
        $sql = "INSERT INTO `admin_log` (`admin_name`, `event`, `ctime`, `ip`, `memo`)
            VALUES ('{$_SESSION['admin_username']}', '{$event}', '".time()."', '".clientIp()."', '{$memo}')";
        $db->query($sql);
    }

    public static function getGmActions()
    {
        $menu = self::getAdminMenu();
        $gm = $menu[32]['sub'];
        $actions = '';
        if($gm){
            foreach($gm as $v){
                if($v['target'] == 'dialog'){
                    $actions .= " <a onclick='javascript:action(this);return false;' class='action-link ui-state-default ui-corner-all' href='{$v['url']}&id={}'><span class='ui-icon ui-icon-newwin'></span>{$v['title']}</a>";
                }else if($v['target'] == 'new_page'){
                    $actions .= " <a target='_blank' class='action-link ui-state-default ui-corner-all' href='{$v['url']}&id={}'><span class='ui-icon ui-icon-newwin'></span>{$v['title']}</a>";
                }else{
                    $actions .= " <a class='action-link ui-state-default ui-corner-all' href='{$v['url']}&id={}'><span class='ui-icon ui-icon-newwin'></span>{$v['title']}</a>";
                }
            }
        }
        return $actions;
    }

    public static function getGmOptions()
    {
        $menu = self::getAdminMenu();
        $gm = $menu[32]['sub'];
        return json_encode($gm);
    }
}
