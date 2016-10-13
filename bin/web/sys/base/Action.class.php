<?php
/*-----------------------------------------------------+
 * 动作类定义
 * @author erlang6@qq.com
 +-----------------------------------------------------*/

abstract class Action{
    public
        /**
         * 访问控制方式
         * ACT_OPEN 开放的
         * ACT_NEED_LOGIN 登录后即可访问
         * ACT_NEED_AUTH 需要进行权限验证 
         *
         * @var int
         */
        $AuthLevel = ACT_NEED_LOGIN;
    /**
     * @var $config define.cfg.php定义的东西 
     */
    public $config = array();

    protected
        /**
         * 设定动作的回应类型 
         * 'json' 以JSON格式回应内容
         * 'string' 以字符串的格式回应内容
         *
         * @var object
         */
        $responseType = 'json',

        /**
         * 浏览器请求信息的集合
         * (合并了$_GET,$_POST,$_FILE)
         *
         * @var array
         */
        $input;

    public function __construct() {
        $this->input = array_merge($_GET, $_POST, $_FILES);
        $this->config = Defines::init();
    }

    /**
     * 输出当前动作的回应
     * @param mixed $data 回应内容
     */
    public function response($data){
        if('json' == $this->responseType){
            if(!is_array($data)){
                throw new Exception('传递了错误的参数$data在 '.__CLASS__.'::'.__FUNCTION__.' 中');
            }
            echo json_encode($data);
        }else if('string' == $this->responseType){
            echo $data;
        }else{
            throw new Exception('不支持的回应类型');
        }
    }

    /**
     * 缓存特定的url参数到Session中
     * @param array $param 参数列表
     */
    public function addParamCache($param){
        foreach($param as $k=>$v)
			$_REQUEST['ParamCache'][$k] = $v;
    }

    /**
     * 清除缓存的url参数
     */
    public function clearParamCache(){
        $_REQUEST['ParamCache'] = array();
    }
}
