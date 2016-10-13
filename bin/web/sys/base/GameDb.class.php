<?php

/**
 * @copyright iYu
 * @package iYu
 * @link jecelyin@gmail.com
 * @author jecelyin peng
 * 经修改
 * Mysql数据库连接驱动
 */
class GameDb extends Db
{

	private static $gameDbInstance = array();

    public function __construct($cfg) {
        parent :: __construct($cfg);
    }

	/**
	 * 获取单例
	 */
	public static function getGameDbInstance($platformid, $serverid)
	{
        $serverid = Game::getTargetServerId($platformid, $serverid);
        $key = $platformid . '_s' . $serverid;
		if (self::$gameDbInstance[$key])
		{
			return self::$gameDbInstance[$key];
		}
        $dsn = Config::getInstance($key)->get('database');
		try
		{
			self::$gameDbInstance[$key] = new self($dsn);
		} catch (Exception $e)
		{
			throw new Exception('数据库连接失败');
		}
        return self::$gameDbInstance[$key];
	}

	public static function getGameDbInstance2($platformid, $serverid)
	{
        $key = $platformid . '_s' . $serverid;
		if (self::$gameDbInstance[$key])
		{
			return self::$gameDbInstance[$key];
		}
        $dsn = Config::getInstance($key)->get('database');
		try
		{
			self::$gameDbInstance[$key] = new self($dsn);
		} catch (Exception $e)
		{
			throw new Exception('数据库连接失败');
		}
        return self::$gameDbInstance[$key];
	}
}
