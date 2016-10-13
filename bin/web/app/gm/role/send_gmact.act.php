<?php
/*-----------------------------------------------------+
 * 给角色进程发送消息
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/
class Act_Send_gmact extends GmAction{

    public $options = array(
        '0'   => '永久',
        '72'  => '72小时',
        '168' => '一星期',
        '720' => '一个月',
        '24'  => '24小时',
        '12'  => '12小时',
        '6'   => '6小时',
        '3'   => '3小时',
        '1'   => '1小时',
    );

    public function __construct(){
        parent::__construct();
    }

    // 扩展参数
    public function params(){
        return array(
            'cmd' => $this->input['cmd'],
            'serverid' => $this->input['serverid'],
            'platformid' => $this->input['platformid'],
        );
    }

    public function fields(){
        if(in_array($this->input['cmd'], array(1, 3, 5))){
            return array(
                'hours' => array(
                    'name' => '时间',
                    'attr' => '',
                    'options' => $this->options,
                    'tips' => '',
                ),
                'reason' => array(
                    'name' => '理由',
                    'attr' => '',
                    'tips' => '',
                ),
            );
        }else{
            return array(
                'reason' => array(
                    'name' => '理由',
                    'attr' => '',
                    'tips' => '',
                ),
            );
        }
    }

    public function action(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            echo '参数(id)错误!';
            return;
        }
        if(!isset($this->input['cmd']) || !is_numeric($this->input['cmd'])){
            echo '参数(cmd)错误!';
            return;
        }
        if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
            echo '参数(serverid)错误!';
            return;
        }
        if(!isset($this->input['platformid'])){
            echo '参数(platformid)错误!';
            return;
        }
        $id = $this->input['id'];
        $cmd = $this->input['cmd'];
        $reason = $this->input['reason'];
        if(isset($this->input['hours']) && $this->input['hours'] > 0){
            $hours = $this->input['hours'];
            $time = time() + $hours * 3600;
        }else{
            $hours = 0;
            $time = 0;
        }
        $gmact = "gmact2|{$id}|{$cmd}|{$reason}|{$time}";
        // <option value="1">封停</option>
        // <option value="2">解封</option>
        // <option value="3">封IP</option>
        // <option value="4">解IP</option>
        // <option value="5">禁言</option>
        // <option value="6">解禁</option>
        $result = GmAction::send($gmact, $this->input['serverid'], $this->input['platformid']);
        if($result['ret'] == 0 && $result['msg'] == 'ok'){
            $logType = 110 + $cmd;
            switch($cmd){
            case 1:
                $logContent = "封停了{$this->input['id']}\n时间：{$this->options[$hours]}\n理由：{$reason}";
                break;
            case 2:
                $logContent = "解封了{$this->input['id']}\n理由：{$reason}";
                break;
            case 3:
                $logContent = "封了{$this->input['id']}的IP\n时间：{$this->options[$hours]}\n理由：{$reason}";
                break;
            case 4:
                $logContent = "解封了{$this->input['id']}的IP\n理由：{$reason}";
                break;
            case 5:
                $logContent = "禁言了{$this->input['id']}\n时间：{$this->options[$hours]}\n理由：{$reason}";
                break;
            case 6:
                $logContent = "解禁了{$this->input['id']}\n理由：{$reason}";
                break;
            default:
                $logContent = "";
                break;
            }
            Admin::log($logType, $logContent);
            Game::setStatus($id, $cmd, $time, $this->input['serverid'], $this->input['platformid']);
            echo "<script language=JavaScript>location.replace(location.href);</script>";
        }
        echo $result['msg'];
    }
}
