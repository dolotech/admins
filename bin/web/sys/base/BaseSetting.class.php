<?php
/**
 * 后台配置管理器，因为只供后台使用，暂不做缓存处理
 * @author 彭 qq:249828165
 */
class BaseSetting
{
	/**
	 * 获取数据
	 * @param string $key
	 * @return any
	 */
	public static function get($key)
	{
		if(!$key)return false;
		$rt = Db::getInstance()->getOne("select `content` from base_setting where `id`='{$key}'");
		if($rt)
			return json_decode($rt, 1);
			
		return $rt;
	}
	
	/**
	 * 设置配置值
	 * @param string $key 键名
	 * @param any $value 任何可以被 json_encode 的类型,使用json更利于数据库保存和导出
	 */
	public static function set($key, $value)
	{
		$value = addslashes(json_encode($value));
		return Db::getInstance()->exec("replace into base_setting (`id`,`content`) values('{$key}','{$value}')");
	}
}
