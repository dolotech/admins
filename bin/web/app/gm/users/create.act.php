<?php
/********************************************************
 *
 * 添加后台管理员
 *
 * @package admin
 * @const TABLE_NAME
 * @author Tim <mianyangone@gmail.com> 
 * @date 2009-10-29 下午05:18:35
 *
 *******************************************************/ 

class Act_Create extends Page{

    const TABLE_NAME = 'base_admin_user';

    public function __construct(){
        parent::__construct();
        $this->assign('goback', Admin::url('list', '', '', true));
        $tmp = Admin::getUserGroup();
        $group= array();
        foreach($tmp as $gid=>$gv){
			if(!$gid){//非超级管理员不能设置超过管理员
				if($_SESSION['admin_group_id'])
					continue;
			}
			$group[$gid] = $gv['name'];
		}
        $this->assign('group', Form::select('items[group_id]', $group));
    }

    public function process(){
        if (!isset($this->input['submit'])){
            $this->showPage();
            return;
        }
        $data = $this->input['items'];
        $emsg = $this->validate($data);
        if ($emsg){
            $this->assign('emsg',$emsg);
            $this->assign('data',$data);
            $this->showPage();
            return;
        }  		
        unset($data['passwd1']);   		
        $data['passwd'] = md5($data['passwd']);
        $data['status'] = 1;
        if($data['group_id']){
            $data['platforms'] = implode(',', $this->input['platforms']);
        };
        $sql = Db::getInsertSql(self::TABLE_NAME,$data);

        Db::getInstance()->exec($sql);   		
        Admin::redirect(Admin::url('list', '', '', true));  		
    }

    /**
     * 检查提交数据的有效性
     * @param array $data
     * @return array
     */
    private function validate(array $data){
        $emsg = array();    	
        if (!$data['username']) 	$emsg['username'] = '用户名不能为空';
        if (!$data['name']) 		$emsg['name'] = '名字不能为空';
        if (!trim($data['passwd']) || $data['passwd'] != $data['passwd1']) $emsg['passwd']= '密码不能为空或你输入的两次密码不一致';
        if (TRUE === $this->checkIsExists($data['username'])) $emsg['username'] = '用户名已存在，请另外选择一个用户名';
		if($_SESSION['admin_group_id'] && !$data['group_id'])
			$emsg['group_id'] = '不能设置为超级管理员';
        return $this->errorMessageFormat($emsg);
    }

    /**
     * 检查用户名是否存在
     * @param string $username
     * @return bool
     */
    private function checkIsExists($username){
        if (!trim($username)) return;    	
        $sql = "select id from " . self::TABLE_NAME . " where username = '{$username}'";
        $rs = Db::getInstance()->getOne($sql);
        if($rs) return true;
	   return false;
    }

    private function showPage($msg=''){
        $this->assign('message',$msg);
        $this->display();    	
    }
}
