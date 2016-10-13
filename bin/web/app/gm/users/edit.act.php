<?php
/*-----------------------------------------------------+
 * 编辑帐号资料
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Edit extends Page{
    public function __construct(){
        parent::__construct();
        $this->assign('goback', Admin::url('list', '', '', true));
    }

    /**
     * 执行入口
     */
    public function process(){
        if(!isset($this->input['id']) || !is_numeric($this->input['id'])){
            throw new NotifyException('参数错误!');
        }
        
        $tmp = Admin::getUserGroup();
        $group= array();
        foreach($tmp as $gid=>$gv){
            if(!$gid){
                //非超级管理员不能设置超级管理员
				if($_SESSION['admin_group_id'])
					continue;
			}
			$group[$gid] = $gv['name'];
		}
        
        $this->assign('members', Db::getInstance()->getAll("select username from base_admin_user"));
        $id = $this->input['id'];
        if(!isset($this->input['submit'])){
            //用户是否点击了提交？如果没有则显示页面
            $info = $this->getInfo($id);
            $info['members'] = $info['members'] ? explode(',',$info['members']) : array();
            $this->assign('data', $info);
            $this->assign('group', Form::select('items[group_id]', $group, $info['group_id']));
            $this->display();
            return;
        }

        $info = $this->input['items'];
        $emsg = $this->validate($info);
        if(count($emsg)){ 
            //用户提交的数据有错误，显示带有错误提示的页面
            $this->assign('emsg', $emsg);
            $this->assign('data', $info);
            $this->assign('group', Form::select('items[group_id]', $group, $info['group_id']));
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
        unset($data['username']); //不可更改帐号名
        if(strlen($data['passwd'])){
            $data['passwd'] = md5($data['passwd']);
        }else{
            unset($data['passwd']);
        }
        $data['error_num'] = (int)$data['error_num'];
        $data['group_id'] = (int)$data['group_id'];
        $data['members'] = implode(',', $data['members']);
        if($data['group_id']){
            $data['platforms'] = implode(',', $this->input['platforms']);
        };
        $dbh = Db::getInstance();
        $sql = $dbh->getUpdateSql('base_admin_user', 'id', $data);
        $dbh->exec($sql);
    }

    /**
     * 取得指定帐号的资料
     * @param int $id 帐号ID
     * @return array 帐号资料
     */
    private function getInfo($id){
        $sql = "select * from base_admin_user where id={$id}";
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

        if(!strlen($data['name'])){
            $emsg['name'] = '真实姓名不能为空';
        }
		if($_SESSION['admin_group_id'] && !$data['group_id'])
			$emsg['group_id'] = '不能设置为超级管理员';
        return $this->errorMessageFormat($emsg);
    }
}
