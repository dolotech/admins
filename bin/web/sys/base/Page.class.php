<?php
/*-----------------------------------------------------+
 * 页面形式的动作
 * @author erlang6@qq.com
 +-----------------------------------------------------*/

abstract class Page extends Action {
    private
        $eReport,
        $layoutFile,
        $blocks= array(),
        $blocksStack= array(),
        $tplFile= array(),
        $pagevar= array(),
        $contents;

    public function __construct() {
        parent :: __construct();
    }
    
    public function setPlatformidServerid()
    {
        if(isset($this->input['platformid']) && $this->input['platformid'] != ''){
            $this->platformid = $this->input['platformid'];
            if(isset($this->input['serverid']) && $this->input['serverid'] > 0){
                $this->serverid = $this->input['serverid'];
                if(!Game::checkPlatformServer($this->platformid, $this->serverid)){
                    $this->serverid = 1;
                }
                setcookie('serverid', $this->serverid, time() + 3600 * 24 * 30);
            }else{
                $this->serverid = 1;
            }
            setcookie('platformid', $this->platformid, time() + 3600 * 24 * 30);
        }else if(isset($_COOKIE['platformid'])){
            $this->platformid = $_COOKIE['platformid'];
            if(isset($_COOKIE['serverid'])){
                $this->serverid = $_COOKIE['serverid'];
                if(!Game::checkPlatformServer($this->platformid, $this->serverid)){
                    $this->serverid = 1;
                }
            }
        }else{
            list($pid, $sid) = Game::getDefaultPlatformServer();
            $this->platformid = $pid;
            $this->serverid = $sid;
            setcookie('platformid', $this->platformid, time() + 3600 * 24 * 30);
            setcookie('serverid', $this->serverid, time() + 3600 * 24 * 30);
        }
    }

    public function getNodeHtml($name='node', $val='', $ext='', $filter_node=array())
    {
        $key = 'admin_erlang_conn' . Config::getInstance()->get('server_id');
    	if(!$val && isset($this->input['node']))
    		$val = $this->input['node'];
    	
    	if(!$_SESSION[$key])
    		new Erlang();
    	$erl = $_SESSION[$key];
    	$nodes[1] = $erl['node'];
    	return Form::select($name, $nodes, $val,false, $ext);
    }

    /**
     * 使用布局页面
     */
    private function setLayout($filename){
        $this->layoutFile = APP_DIR.'/'.APP_ID.'/_layout/'.$filename.'.layout.htm';
    }

    private function loadBlock($name){
        echo $this->blocks[$name];
    }

    /**
     * 块定义开始
     */
    private function block($name){
        array_push($this->blocksStack, $name);
        ob_start();
    }

    /**
     * 块定义结束
     */
    private function endBlock($endName){
        $name = array_pop($this->blocksStack);
        if($name != $endName){
            throw new Exception('区块定义有误，未配对或有交叉');
        }
        $this->blocks[$name] = ob_get_clean();
    }

    /**
     * 编译模板
     */
    private function compile() {
        $eReport = error_reporting();
        error_reporting(E_ALL ^E_NOTICE);
        // $ts = time();
		// $key = Admin::getTicket($ts);
		// $uname = $_SESSION['admin_username'];
		// $purl = "img-csss/?mod=default&act=login&admin_key={$key}&username={$uname}&ts={$ts}";
        // 
		// $this->assign("ADMIN_PURL", $purl);
        extract($this->pagevar);

        ob_start();
        foreach ($this->tplFile as $file) {
            if($file['absolute'])
                include $file['filename'];
            else include APP_ROOT.'/_tpl/'.$file['filename'];
        }
        $this->contents= ob_get_contents();
        ob_end_clean();

        if($this->layoutFile){
            ob_start();
            include $this->layoutFile;
            $this->contents= ob_get_contents();
            ob_end_clean();
        }
        error_reporting($eReport);
    }

    /**
     * 添加模板
     * @param string $filname 模板文件名
     * @param bool $absPath 模板文件是否用绝对表示的
     */
    public function addTemplate($filename, $absPath=false) {
        if(!$absPath) $filename .= '.tpl.htm';
        $this->tplFile[]= array(
            'filename' => $filename
            ,'absolute' => $absPath
        );
    }

    /**
     * 清空模板
     * @param string $filname 模板文件名
     * @param bool $absPath 模板文件是否用绝对表示的
     */
    public function clearTemplate() {
        $this->tplFile = array();
    }

    /**
     * 添加模板变量
     *
     * @param string $key 变量名
     * @param mixed $var 变量值
     */
    public function assign($key, $var) {
        $this->pagevar[$key] = $var;
    }

    /**
     * 编译并返回模板内容
     *
     * @return string
     */
    public function fetch() {
        //如果没有添加任何模板则默认使用与当前动作同名的模板
        if(!count($this->tplFile)){
            $this->addTemplate(CURRENT_ACTION);
        }
        $this->compile($this->pagevar);
        return $this->contents;
    }

    /**
     * 显示模板(即输出模板内容)
     */
    public function display() {
        echo $this->fetch();
    }

    /**
     * 错误信息格式化
     * @param array $emsg 错误信息
     * @return array 格式化后的错误信息
     */
    public function errorMessageFormat($emsg){
        $rtn = array();
        foreach($emsg as $k=>$v){
            if(is_array($v)){
                $rtn[$k] = $this->errorMessageFormat($v);
            }else{
                $rtn[$k] = "<span class='err'>$v</span>";
            }
        }

        return $rtn;
    }
}
