<?php

/**
 * @copyright iYu
 * @package iYu
 * @link jecelyin@gmail.com
 * @author jecelyin peng
 * 经修改
 * Mysql数据库连接驱动
 */
class Db
{

	//当前连接
	public $link = null;
	public $dbname = null;
	//表前缀
	public $prefix = '';
	//最后插入ID
	public $insert_id = 0;
	//查询次数
	public $count = 0;
	private static $instance = array();
	public static $tCouter = 0;

    public function __construct($cfg)
    {
        $this->link = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['dbname']);
        if ($this->link->connect_error || $this->link === null) {
            $this->_halt('无法连接到数据库主机：' . $cfg['host']);
            $this->link = null;
        } else {
            $this->link->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
            $this->dbname = $cfg['dbname'];
        }
        unset($cfg);
        return $this->link;
    }

	/**
	 * 获取单例
	 */
	public static function getInstance($db = "database")
	{
		if (self::$instance[$db])
		{
			return self::$instance[$db];
		}

		$dsn = Config::getInstance()->get($db);
		try
		{
			self::$instance[$db] = new self($dsn);
		} catch (Exception $e)
		{
			throw new Exception('数据库连接失败');
		}

		return self::$instance[$db];
	}

	public function query($sql)
	{
		$sql = str_replace('@#@_', $this->prefix, $sql);
        $query_id = $this->link->query($sql);
        if ($query_id === false || $this->link->error) {
            $this->_halt('查询失败：' . str_replace(array("\n", "\r"), '', $sql));
        }
		$this->count++;
		$sql = trim($sql);
		//删除或更新时返回影响行数
        // if (preg_match("/^(delete|update) /i", $sql)) {
        //     return $this->link->affected_rows;
        // }
		// //插入或替换时返回最后影响的ID
		// if (preg_match("/^(insert|replace) /i", $sql))
		// {
        //     return $this->link->insert_id;
		// }
		return $query_id;
	}

	public function exec($sql)
	{
		$this->query($sql);
		return $this->link->affected_rows;
	}

	/**
	 * 跟据数组中的数据返回一条插入语句
	 * (目前只考虑Mysql支持)
	 *
	 * @param string $table 表名
	 * @param array $data 数据
	 * @return string SQL语句
	 */
	public static function getInsertSql($table, $data, $addQuotes=True)
	{
		//peng：这里不应该再转义一次
		if($addQuotes)
		    $data = addQuotes($data);

		$col = array();
		$val = array();
		foreach ($data as $k => $v)
		{
			if (null === $v) continue;
			$col[] = $k;
			$val[] = $v;
		}
		return "insert into `{$table}`(`" . implode($col, '`, `') . "`) values('" . implode($val, "', '") . "')";
	}

	/**
	 * 跟据数组中的数据返回一条更新语句
	 * (目前只考虑Mysql支持)
	 *
	 * @param string $table 表名
	 * @param string|array $primaryKey 主键名，多主键时用数组传递
	 * @param array $data 数据
	 * @return false|string false或SQL语句
	 */
	public static function getUpdateSql($table, $primaryKey, $data, $addQuotes=True)
	{
		if($addQuotes)
		    $data = addQuotes($data);

		$w = array();
		if (is_array($primaryKey))
		{
			foreach ($primaryKey as $v)
			{
				$w[] = "`$v`='{$data[$v]}'";
				if (isset($data[$v]))
				{
					unset($data[$v]);
				} else
				{
					return false;
				}
			}
		} else if (isset($data[$primaryKey]))
		{
			$w[] = "`$primaryKey`='{$data[$primaryKey]}'";
			unset($data[$primaryKey]);
		} else
		{
			return false;
		}

		if (!$data) return false;

		$u = array();
		foreach ($data as $k => $v)
		{
			if (null === $v) continue;
			$u[] = "`{$k}`='{$v}'";
		}
		return "update `{$table}` set " . implode($u, ', ') . " where " . implode($w, ' and ');
	}

	/**
	 * 执行一个Limit查询
	 * 用法示例:
	 * $dbh = Db::getInstance();
	 * $sql = 'select * from user';
	 * $rs = $dbh->selectLimit($sql, 0, 10); //取出第0行开始的10条数据
	 * while($row = $rs->fetch()){
	 *     print_r($row);
	 * }
	 *
	 * @param string $sql SQL语句
	 * @param int $offset 偏移量
	 * @param int $num 要求返回的记录数
	 * @return Object 返回一个结果集句柄
	 */
	public function selectLimit($sql, $offset, $num)
	{
		$sql .= " limit $offset, $num";
		$rs = $this->query($sql);
		return $rs;
	}

	public function fetch_array($queryId, $result_type = 'assoc')
	{
        if ($result_type == 'object') {
            return $queryId->fetch_object();
        } else {
            return $queryId->fetch_assoc();
        }
	}

	public function getRow($query, $result_type = 'assoc')
	{
		return $this->fetch_array($this->query($query), $result_type);
	}

	public function getOne($query, $offset = 0)
	{
		$result = $this->query($query)->fetch_row();
		return $result === false ? false : $result[0];
	}

	public function getAll($query, $result_type = 'assoc')
	{
		$query_id = $this->query($query);
		$cacheArray = array();
		while ($result = $this->fetch_array($query_id, $result_type)) {
			$cacheArray[] = $result; //trim($result);
		}
		return $cacheArray;
	}

	public function getAllCol1($query)
	{
		$query_id = $this->query($query);
		$cacheArray = array();
		while ($result = $query_id->fetch_row()) {
			$cacheArray[] = $result[0]; //trim($result);
		}
		return $cacheArray;
	}

	public function insert_id()
	{
        return $this->link->insert_id;
	}

	public function getSql($action, $table, $data, $where = array())
	{
		switch (strtolower($action))
		{
			case 'insert':
			case 'replace':
				$fields = array_keys($data);
				return strtoupper($action) . " INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $data) . "')";
			case 'update':
				$sp = $s = $w = '';
				foreach ($data as $k => $v)
				{
					$s .= $sp . "`$k` = '{$v}'";
					$sp = ', ';
				}
				if ($where)
				{
					$sp = '';
					if (is_array($where)) foreach ($where as $k => $v)
						{
							$w .= $sp . "`$k` = '$v'";
							$sp = ' AND ';
						}
					else $w = $where;
				}
				return strtoupper($action) . " `{$table}` SET $s WHERE $w";
		}
	}

	public function insert($table, $data)
	{
		return $this->query($this->getSql('insert', $table, $data));
	}

	public function replace($table, $data)
	{
		return $this->query($this->getSql('replace', $table, $data));
	}

	/**
	 * @param $table 要更新的表名
	 * @param $data 要更新的数据的数组，其中数组的key对应字段名，值对应字段的值
	 * @param $where 更新对象的数组或字符串
	 */
	public function update($table, $data, $where)
	{
		return $this->query($this->getSql('update', $table, $data, $where));
	}

	public function affected_rows()
	{
        return $this->link->affected_rows;
	}

	public function num_rows($sql)
	{
		return $this->query($sql)->num_rows;
	}

	private function _halt($msg)
	{
		if(!DEBUG)exit('invalid query.');
		$error = $this->link->error;
		$errno = $this->link->errno;
		$debug_info = debug_backtrace();
		$debug_info = array_reverse($debug_info);
		$err_html = '';
		$err_html .= "<b>Database error:</b><br /> $msg <br />";
		$err_html .= "<b>MySQL Error:</b><br />errno: {$errno} <br />error: {$error}<br /><br />";
		foreach ($debug_info as $v)
			if (isset($v['file'])) $err_html .= "<b>File:</b> {$v['file']} (Line: {$v['line']})<br />";
        echo $err_html;
		exit();
	}

}
