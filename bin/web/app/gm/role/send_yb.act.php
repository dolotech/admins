<?php

/* *******************************************************
 *
 * 发送货币
 *
 * ***************************************************** */

class Act_Send_yb extends GmAction
{

    public function __construct()
    {
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
            'reward_type' => array(
                'name' => '类型',
                'attr' => '',
                'options' => array(
                    '901' => '维护补偿',
                    '902' => 'BUG补偿',
                    '903' => '活动异常补偿',
                    '904' => '奖励补发',
                    '905' => '充值补发',
                    '906' => '活动奖励',
                    '907' => '帮派奖励',
                    '908' => '论坛活动奖励',
                    '999' => '后台奖励',
                ),
                'tips' => ' [充值补发] 走正常的充值流程，慎用！',
            ),
            'yb' => array(
                'name' => '数量',
                'attr' => '',
                'tips' => '',
            ),
        );
    }

    public function action()
    {
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            echo '参数(id)错误!';
            return;
        }
        if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
            echo '参数(serverid)错误!';
            return;
        }
        if(!isset($this->input['yb']) || !is_numeric($this->input['yb']) || $this->input['yb'] <= 0){
            echo '元宝数量不正确！';
            return;
        }
        $cmd = "gmreward|{$this->input['id']}|1|1|{\"ext\":{\"1\":\"{$this->input['yb']}\"}}|0|0|0|1|{$this->input['reward_type']}";
        $result = GmAction::send($cmd, $this->input['serverid'], $this->input['platformid']);
        if($result['ret'] == 0){
            Admin::log(4, $cmd);
        }
        echo $result['msg'];
    }
}
