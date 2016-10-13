<?php
/**
 * 后台配置管理器，因为只供后台使用，暂不做缓存处理
 * @author 彭 qq:249828165
 */
class AdminConf
{
	/**
	 * 获取数据
	 * @param string $key
	 * @return any
	 */
	public static function get($key)
	{
		if(!$key)return false;
		$rt = Db::getInstance()->getOne("select `value` from admin_config where `key`='{$key}'");
		if($rt)
			return unserialize($rt);
			
		return $rt;
	}
	
	/**
	 * 设置配置值
	 * @param string $key 键名
	 * @param any $value 任何可以被 serialize 的类型
	 */
	public static function set($key, $value)
	{
		$value = serialize($value);
		return Db::getInstance()->exec("replace into admin_config (`key`,`value`) values('{$key}','{$value}')");
	}
}