<?php
/*-----------------------------------------------------+
 * 设置内部账号
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/
class Act_Set_local_player extends GmAction{
    public function __construct(){
        parent::__construct();
    }

    // 扩展参数
    public function params(){
        return array(
            'platformid' => $this->input['platformid'],
            'serverid' => $this->input['serverid'],
            'account' => $this->input['account'],
            'id' => $this->input['id'],
            'do' => $this->input['do'],
        );
    }

    public function action(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            echo '参数(id)错误!';
            return;
        }
        if(!isset($this->input['account'])){
            echo '参数(account)错误!';
            return;
        }
        if(!isset($this->input['serverid']) || !is_numeric($this->input['serverid'])){
            echo '参数(serverid)错误!';
            return;
        }
        $playerId = $this->input['id'];
        $account = $this->input['account'];
        $serverId = $this->input['serverid'];
        $platformId = $this->input['platformid'];

        if($this->input['do'] == 'del'){
            $sql1 = "delete from game_local_account where account = '{$account}';";
        }else{
            $sql1 = "REPLACE INTO game_local_account (account) VALUES ('{$account}');";
        }
        Db::getInstance()->exec($sql1);
        echo '设置成功，刷新页面后会更新名字显示状态！';
    }
}
