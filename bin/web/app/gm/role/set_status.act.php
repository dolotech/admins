<?php
/*-----------------------------------------------------+
 * 给角色进程发送消息
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/
class Act_Set_status extends GmAction{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        if(!isset($this->input['id']) || !isset($this->input['serverid'])){
            throw new NotifyException('参数错误!');
        }
        $id = (int)$this->input['id'];
        $id = (int)$this->input['serverid'];

        Admin::redirect(Admin::url('list', '', '', true));
    }

    public function action(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            echo '参数(id)错误!';
            return;
        }
        $id = $this->input['id'];
        switch($this->input['cmd']){
        case '1' :
            if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
                echo '参数(serverid)错误!';
                return;
            }
            $result = $this->send("gmact2|{$this->input['id']}|1|ss|1434620016", $this->input['serverid']);
            if($result['ret'] == 0){
                Admin::log(2, 'ID：'.$this->input['id']);
                Game::setStatus($id, 1, $this->input['serverid']);
            }
            echo $result['msg'];
            break;
        case '2' :
            if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
                echo '参数(serverid)错误!';
                return;
            }
            $result = $this->send("gmact2|{$this->input['id']}|2|ss|1434620016", $this->input['serverid'], $this->input['serverid']);
            if($result['ret'] == 0){
                Admin::log(3, 'ID：'.$this->input['id']);
                Game::setStatus($id, 2, $this->input['serverid']);
            }
            echo $result['msg'];
            break;
        }
    }
}
