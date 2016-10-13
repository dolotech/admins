<?php
/*-----------------------------------------------------+
 * 编辑帐号资料
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Set_Password extends Page{
    public function __construct(){
        parent::__construct();
        $this->assign('goback', Admin::url('list', '', '', true));
    }

    /**
     * 执行入口
     */
    public function process(){
        if(!isset($this->input['submit'])){
            //用户是否点击了提交？如果没有则显示页面
            $info = $this->getInfo();
            $this->assign('data', $info);
            $this->display();
            return;
        }

        $info = $this->input['items'];
        $emsg = $this->validate($info);
        if(count($emsg)){ 
            //用户提交的数据有错误，显示带有错误提示的页面
            $this->assign('emsg', $emsg);
            $this->assign('data', $info);
            $this->display();
            return;
        }

        $this->update($info);

        Admin::redirect(Admin::url('list', '', '', true)); //页面跳转
    }

    /**
     * 执行更新
     * @param array $data 帐号数据
     */
    private function update($data){
        unset($data['passwd1']);
        if(strlen($data['passwd'])){
            $data['passwd'] = md5($data['passwd']);
        }else{
            unset($data['passwd']);
        }
        $data['id'] = $_SESSION['admin_uid'];
        $dbh = Db::getInstance();
        $sql = $dbh->getUpdateSql('base_admin_user', 'id', $data);
        $dbh->exec($sql);
    }

    /**
     * 取得指定帐号的资料
     * @param int $id 帐号ID
     * @return array 帐号资料
     */
    private function getInfo(){
        $sql = "select * from base_admin_user where id={$_SESSION['admin_uid']}";
        return Db::getInstance()->getRow($sql);
    }

    /**
     * 校验用户提交的数据
     * @param array $data 帐号数据
     * @return array 错误提示信息
     */
    private function validate($data){
        $emsg = array();
        if($data['passwd'] && ($data['passwd'] != $data['passwd1'])){
            $emsg['passwd'] = '两次输入的密码不一致';
        }
        return $this->errorMessageFormat($emsg);
    }
}
