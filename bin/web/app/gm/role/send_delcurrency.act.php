<?php
/*-----------------------------------------------------+
 * 给角色进程发送消息
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

// %% type ( 1 == rmb, 2 == bind rmb, 3 == coin, 4 == spirit )
// handle([<<"gmdelcurrency">>, UIDBin, TypeBin, NumBin], Socket)->
// uid：玩家id
// num:扣除数量

class Act_Send_delcurrency extends GmAction{

    public $options = array(
        '1'   => '元宝',
        '2'  => '礼金',
        '3' => '银两',
        '4' => '灵气',
    );

    public function __construct(){
        parent::__construct();
    }

    // 扩展参数
    public function params(){
        return array(
            'serverid' => $this->input['serverid'],
            'platformid' => $this->input['platformid'],
        );
    }

    public function fields(){
            return array(
                'type' => array(
                    'name' => '类型',
                    'attr' => '',
                    'options' => $this->options,
                    'tips' => '',
                ),
                'num' => array(
                    'name' => '数量',
                    'attr' => '',
                    'tips' => '',
                ),
                'reason' => array(
                    'name' => '理由',
                    'attr' => '',
                    'tips' => '',
                ),
            );
    }

    public function action(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            echo '参数(id)错误!';
            return;
        }
        if(!isset($this->input['type']) || !is_numeric($this->input['type'])){
            echo '参数(type)错误!';
            return;
        }
        if(!isset($this->input['num']) || !is_numeric($this->input['num'])){
            echo '参数(num)错误!';
            return;
        }
        if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
            echo '参数(serverid)错误!';
            return;
        }
        $id = $this->input['id'];
        $type = $this->input['type'];
        $num = $this->input['num'];
        $command = "gmdelcurrency|{$id}|{$type}|{$num}";
        $result = GmAction::send($command, $this->input['serverid'], $this->input['platformid']);
        if($result['ret'] == 0 && $result['msg'] == 'ok'){
            $logType = 6;
            $logContent = "扣除了{$this->input['id']}的{$this->options[$type]}\n";
            $logContent .= "数量：{$num}\n理由：{$this->input['reason']}";
            Admin::log($logType, $logContent);
        }
        echo $result['msg'];
    }
}
