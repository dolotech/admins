<?php
/*-----------------------------------------------------+
 * 删除帐号
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Delete extends Action{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            throw new NotifyException('参数错误!');
        }
        $id = $this->input['id'];

        $sql = "delete from base_admin_user where id={$id}";
        $affetcedRows = Db::getInstance()->exec($sql);
        Admin::redirect(Admin::url('list', '', '', true));
    }
}
