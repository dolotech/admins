<?php

/* * *************************************************************
 * 系统用户登录
 *
 * @author yeahoo2000@gmail.com
 * ************************************************************* */

class Act_Login extends Page
{

	public $AuthLevel = ACT_OPEN;

	public function __construct()
	{
		parent::__construct();
	}

	public function process()
	{
		$this->input = trimArr($this->input);
		$this->input['ref'] = urldecode($this->input['ref']);
		if ($this->input['ref'])
		{
			$ref = urldecode($this->input['ref']);
		} else
		{
			$ref = Admin::url('index');
		}
		
		if (isset($this->input['xml']))
		{
			if (isset($this->input['username']) && isset($this->input['passwd']))
			{
				$success = $this->login($this->input['username'], $this->input['passwd']);
			} else
			{
				$success = false;
			}
			header('content-type: text/xml');
			echo arrayToXml(array('success' => $success));
			return;
		} elseif (isset($this->input['admin_key']) && isset($this->input['username']))
		{
			if (AllApi::isVaildTicket())
			{
				$passwd = Db::getInstance()->getOne("select passwd from  `base_admin_user` where username = '{$this->input['username']}'");
				$this->login($this->input['username'], $passwd, true);
				Admin::redirect($ref);
				exit;
			}
		}

		if (!isset($this->input['username']))
		{
			$this->showPage();
			return;
		}

		if (!$this->login($this->input['username'], $this->input['passwd']))
		{

			$db = Db::getInstance();
			if (!$db->getOne("select count(*) as c from base_admin_user"))
			{
				$this->showPage('已经初始化了一个超级管理员帐号，请及时修改密码!');
				$db->query("INSERT INTO `base_admin_user` (`id`, `username`, `status`, `passwd`, `name`, `description`, `last_visit`, `last_ip`, `last_addr`, `login_times`,group_id) VALUES (NULL, 'turbotech', '1', '" . md5('turbotech26076116') . "', 'turbotech', '', '0', '', '', '0',0)");
				return;
			}
			$this->showPage('认证失败，你输入的用户名或密码不正确!', $this->input['username']);
			return;
		}

		Admin::redirect($ref);
	}

	/**
	 * 登录
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 * @return 是否成功
	 */
    private function login($username, $passwd, $md5pwd=false)
    {

        $info = $this->getUserinfo($username);
        $db = Db::getInstance();
        $now=time();
        $ip = clientIp();
        //5次错误 and 封锁15分钟
        if($info['error_num'] >= 15 && ($now-$info['error_time'] < 15*60))
        {
            $this->showPage("你输入的错误次数过多, 系统已经禁止你的登录!", $username);
            exit;
        }
        $ret = $this->login2($username, $passwd, $md5pwd);
        if($ret)
            return $ret;
        //5次错误
        if($info['error_num'] >= 15)
        {
            //封锁15分钟
            if($now-$info['error_time'] > 15*60)
            {
                $db->query("update base_admin_user set error_time='{$now}',error_ip='{$ip}', error_num='0' where username='$username'");
            }
        }else{
            $db->query("update base_admin_user set error_time='{$now}',error_ip='{$ip}', error_num=error_num+1 where username='$username'");
        }
        return $ret;
    }


	private function login2($username, $passwd, $md5pwd=false)
	{
		$info = $this->getUserinfo($username);
		$mpwd = $md5pwd ? $passwd : md5($passwd);
		if ($mpwd != $info['passwd'])
		{
			return false;
		}

		if (!$info['status'])
		{
			return false;
		}

        $ip = clientIp();
        if(trim($info['ip_limit']))
        {
            $ip_limit = explode(',', $info['ip_limit']);
            if(!in_array($ip, $ip_limit))
                return false;
        }
        
        //已经登录就不记录了
        if (isset($_SESSION['admin_name']) && $_SESSION['admin_name'] == $info['name'])
            $log = false;
        else
            $log = true;

		//登录成功
		$_SESSION['admin_uid'] = $info['id'];
		$_SESSION['admin_name'] = $info['name'];
		$_SESSION['admin_username'] = $info['username'];
		$_SESSION['admin_passwd'] = $info['passwd'];
		$_SESSION['admin_group_id'] = $info['group_id'];
        if($info['group_id'] && $info['platforms']){
            $_SESSION['admin_platforms'] = explode(',', $info['platforms']);
        }
		$_SESSION['admin_last_action_time'] = time();
        if($log)
            Admin::log(1, '');
        
		$db = Db::getInstance();
		$sql = $db->getUpdateSql('base_admin_user', 'id', array(
					'last_visit' => time(),
					'last_ip' => $ip,
					'last_addr' => Utils::ip2addr(clientIp()),
					'login_times' => $info['login_times'] + 1,
					'id' => $info['id']
				));
		$db->exec($sql); //更新登录记录
		return true;
	}

	private function showPage($msg='', $username='')
	{
		$this->assign('message', $msg);
		$this->assign('username', $username);
		$this->display();
	}

	private function getUserinfo($username)
	{
		$sql = "select * from base_admin_user where username='$username'";
		return Db::getInstance()->getRow($sql);
	}

}
