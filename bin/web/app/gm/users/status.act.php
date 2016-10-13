<?php
/*-----------------------------------------------------+
 * 状态处理
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Status extends Action{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        if(
            !isset($this->input['id'])
            || !isset($this->input['val'])
        ){
            throw new NotifyException('参数错误!');
        }
        $id = $this->input['id'];
        if(is_array($id)){
            $id = implode(',', $id);
        }
        else if(!is_numeric($id)){
			throw new NotifyException('错误! 没有选定任何项目!');
        }

        $val = (int)$this->input['val'];
        $sql = "update base_admin_user set status=$val where id in($id)";
        Db::getInstance()->exec($sql);
        Admin::redirect(Admin::url('list', '', '', true));
    }
}
