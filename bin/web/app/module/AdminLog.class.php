<?php

class AdminLog
{
	public static function editRole($opt)
	{
		$db = Db::getInstance();
		if(!$opt['role_id'])exit('bad');
		$role_name = $db->getOne("select name from role where id={$opt['role_id']}");
		$db->query("INSERT INTO `log_admin_edit_role` (`type`, `val`, `field`, `ctime`, `admin`, role_id, role_name, ip) VALUES ('{$opt['type']}', '{$opt['val']}', '{$opt['field']}', '".time()."', '{$_SESSION['admin_username']}', '{$opt['role_id']}', '{$role_name}', '".clientIp()."')");
	}
}