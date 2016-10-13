<?php

/* *******************************************************
 *
 * 发送货币
 *
 * ***************************************************** */

class Act_Send_item extends GmAction
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
            'itemid' => array(
                'name' => '物品ID',
                'attr' => ' id="itemid" ',
                'tips' => '',
            ),
            'reward_type' => array(
                'name' => '类型',
                'attr' => '',
                'options' => array(
                    '901' => '维护补偿',
                    '902' => 'BUG补偿',
                    '903' => '活动异常补偿',
                    '904' => '奖励补发',
                    '906' => '活动奖励',
                    '907' => '帮派奖励',
                    '908' => '论坛活动奖励',
                    '999' => '后台奖励',
                ),
                'tips' => '',
            ),
            'num' => array(
                'name' => '物品数量',
                'attr' => '',
                'tips' => '数量不要太大(<100)',
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
        if(!isset($this->input['itemid']) || !is_numeric($this->input['itemid']) || $this->input['itemid'] <= 0){
            echo '物品ID不正确！'.$this->input['itemid'];
            return;
        }
        if(!isset($this->input['num']) || !is_numeric($this->input['num']) || $this->input['num'] <= 0 || $this->input['num'] > 100){
            echo '物品数量不正确！';
            return;
        }
        // gmreward|2097153|1|1|{\"goods\":{\"1010018\":\"1\"}}|0|0|0|1|901
        $cmd = "gmreward|{$this->input['id']}|1|1|{\"goods\":{\"{$this->input['itemid']}\":\"{$this->input['num']}\"}}|0|0|0|1|{$this->input['reward_type']}";
        $result = GmAction::send($cmd, $this->input['serverid'], $this->input['platformid']);
        if($result['ret'] == 0){
            Admin::log(5, $cmd);
        }
        echo $result['msg'];
    }
}
