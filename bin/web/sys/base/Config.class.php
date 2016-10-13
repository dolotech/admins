<?php
/*-----------------------------------------------------+
 * 配置处理类
 * @author rolong@vip.qq.com
 +-----------------------------------------------------*/
class Config {

    /**
     * 保存当前的配置内容
     * @var array
     */
    private $conf = array ();

    /**
     * 保存实例
     * @var array
     */
    private static $instance = array();


    private function __construct($file) {
        $this->conf = $this->load($file);
        if($file == 'default'){
        }
    }

    public static function getInstance($file = 'default') {
        if(isset(self::$instance[$file])){
            return self::$instance[$file];
        }
        self::$instance[$file] = new self($file);
        return self::$instance[$file];
    }

    private static function getPlatformsServers(){
        $cfgFiles = glob(CFG_DIR.'/*.cfg.php');
        $platformsServers = array();
        foreach($cfgFiles as $cfgFile){
            $cfgData = include($cfgFile);
            if($cfgData['merge_server_id'] == '0'
                && $cfgData['merge_type'] == '2'
            ) continue;
            $name = basename($cfgFile);
            $name = str_replace('.cfg.php', '', $name);
            $ps = explode('_s', $name);
            if(count($ps) == 2 && $ps[0] != '' && is_numeric($ps[1])){
                $platformsServers[$ps[0]][] = $ps[1];
            }
        }
        $platformsServers1 = array();
        foreach($platformsServers as $k => $v){
            if('gm' == APP_ID 
                && isset($_SESSION['admin_platforms']) 
                && !in_array($k, $_SESSION['admin_platforms'])) 
            {
                continue;
            }
            rsort($v);
            $platformsServers1[$k] = $v;
        }
        return $platformsServers1;
    }

    /**
     * 取得一个配置的值
     *
     * @param string $key 配置名
     * @return mixed
     */
    public function get($key) {
        if(isset($this->conf[$key])) return $this->conf[$key];
        if('platformsServers' == $key){
            $v = self::getPlatformsServers();
            $this->conf['platformsServers'] = $v;
            return $v;
        }
        return '';
    }

    /**
     * 修改或添个一个配置
     *
     * @param string $key 配置名
     * @param mixed $val 值
     * @retrun 该配置的值
     */
    public function set($key, $val){
        return $this->conf[$key] = $val;
    }

    /**
     * 保存配置
     * TODO: 未实现
     */
    public function save(){
    }

    /**
     * 加载配置
     *
     * @return 配置内容
     */
    private function load($fileId){
        $file = CFG_DIR.'/'.$fileId.'.cfg.php';
        if(file_exists($file)){
            return include $file;
        }
        $file = APP_DIR.'/'.$fileId.'.cfg.php';
        if(file_exists($file)){
            return include $file;
        }
        exit("The file does not exist: ".$file);
    }

}
