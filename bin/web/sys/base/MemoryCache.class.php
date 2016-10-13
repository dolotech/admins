<?php
/*-----------------------------------------------------+
 * memcache缓存扩展
 *
 * @author erlang6@qq.com
 +-----------------------------------------------------*/
class MemoryCache extends Memcache{
    private static
        $instance = array();
    private
        $cache = array();
    
    /**
     * 构造函数
     *
     * @param host|IP $host 服务器地址
     * @param int $port 端口号
     */
    private function __construct($host, $port){
        $this->connect($host, $port);
    }

    /**
     * 获取单例
     * @param string $name 配置名，可选，默认为'default'
     */
    public static function getInstance($name='default'){
        if(isset(self::$instance[$name])){
            return self::$instance[$name];
        }

        $dsn = Config::getInstance()->get('memcache');
        if(!isset($dsn[$name])){
            throw new Exception("没有名为'$name'的memcache配置项)");
        }
        $dsn = $dsn[$name];
        try{
            self::$instance[$name] = new self($dsn['host'], $dsn['port']);
        }catch(Exception $e){
            throw new Exception('memcache连接失败');
        }
        return self::$instance[$name];
    }

    /**
     * 重载get()方法，加入缓存机制，对多次取出同一键时有性能提升，缺点是内存使用量会增加
     * @param $key 键名
     * @return mixed 值
     */
    /*
    public function get($key){
        if(is_array($key)){
            return parent::get($key);
        }
        if(!isset($this->cache[$key])){
            $this->cache[$key] = parent::get($key);
        }
        return $this->cache[$key];
    }
     */

    /**
     * 重载set()方法，以配合set()方法的缓存机制
     * @param string $key 键名
     * @param mixed $var 值
     * @param int $flag 是否对值进行压缩
     * @param int $expire 缓存时间
     */
    /*
    public function set($key, $var, $flag=false, $expire=0){
        if($expire > 2592000){ //不能超过30天
            $expire = 2592000;
        }
        parent::set($key, $var, $flag, $expire);
        if(isset($this->cache[$key])){
            $this->get($key);
        }
    }
     */
}
