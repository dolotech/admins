<?php

/* *******************************************************
 *
 * Gm Action Op
 *
 * ***************************************************** */

class GmAction extends Page
{
    public $gm_action_template = 'gm_action';
	public function __construct()
	{
		parent::__construct();
	}

    public function fields(){
        // key 不能为: id | mod | act | rand | op
        //
        // return array(
        //     'money_type' => array(
        //         'name' => '类型',
        //         'attr' => '',
        //         'options' => array(
        //             'diamond' => '钻石',
        //             'gold' => '金币',
        //         ),
        //         'tips' => '',
        //     ),
        //     'money' => array(
        //         'name' => '数量',
        //         'attr' => '',
        //         'tips' => '',
        //     ),
        // );
        return array();
    }

    public function params(){
        // return array(
        //     'id_type' => 'role_id',
        // );
        return array();
    }

    public function process()
    {
        if ($this->input['op'] == 1)
        {
            $this->action();
        }else{
            $fields = $this->fields();
            $params = array();
            foreach($this->params() as $k => $v){
                $params[$k] = '"'.$v.'"';
            }
            $this->clearTemplate();
            $this->addTemplate($this->gm_action_template);
            $this->assign('fields', $fields);
            $this->assign('params', $params);
            $this->display();
        }
    }

    public static function send($message, $serverid, $platformid){
        $tgsid = Game::getTargetServerId2($platformid, $serverid);
		$cfg = Config::getInstance($platformid.'_s'.$tgsid);
        $gmIP = $cfg->get('gmIP');
        $gmPort = $cfg->get('gmPort');
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $msg = "socket_create() failed! IP({$gmIP}), Port({$gmPort})";
            return array('ret' => 1, 'msg' => $msg);
        }
        $result = @socket_connect($socket, $gmIP, $gmPort);
        if($result === false) {
            $msg = "socket_connect() failed! IP({$gmIP}), Port({$gmPort})";
            return array('ret' => 2, 'msg' => $msg);
        }
        $in = 'e820c512c1a2f9aefbc98d76757dd9e2';
        $in .= $message;
        socket_write($socket, $in, strlen($in));
        $data = "";
        // while ($sRead = socket_read($socket, 8192, PHP_NORMAL_READ)) {
        while ($sRead = socket_read($socket, 8192)) {
            $data .= $sRead;
        }
        socket_close($socket);
        return array('ret' => 0, 'msg' => $data);
    }
}
