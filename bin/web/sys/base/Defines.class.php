<?php
/*-----------------------------------------------------+
 * 系统预定义数据操作类
 * @author erlang6@qq.com
 +-----------------------------------------------------*/
class Defines{
    const
        KEY_LIST = 'system_define_list',
        KEY_PREFIX = 'system_define_item_';

   /**
     * 数据初始化，将数据加载到memcache服务器中
     */
    public static function init(){
//        $mc = MemoryCache::getInstance('system');
        $data = self::load();
//        $list = array();
//        foreach($data as $k=>$v){
//            $mc->set(self::KEY_PREFIX.$k, $v);
//            $list[] = $k;
//        }
//        $mc->set(self::KEY_LIST, $list);
        return $data;
    }

    /**
     * 取出指定键的数据
     * @param string $key 键名
     */
    public static function get($key){
//        $mc = MemoryCache::getInstance('system');
//        $data = $mc->get(self::KEY_PREFIX.$key);
//        if(false === $data){
            $d = self::init();
            $data = $d[$key];
//        }
        return $data;
    }

    /**
     * 刷新缓存
     */
    public static function reload(){
        $mc = MemoryCache::getInstance('system');
        $list = $mc->get(self::KEY_LIST);
        if(is_array($list)){
            foreach($list as $v){
                $mc->delete(self::KEY_PREFIX.$v);
            }
        }
        self::init(); //重新初始化
    }

    /**
     * 加载数据
     * @return 内容
     */
    private static function load(){
        return include APP_DIR.'/define.cfg.php';
    }
}

