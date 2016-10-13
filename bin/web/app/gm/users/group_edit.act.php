<?php
/*-----------------------------------------------------+
 * 后台帐号列表
 +-----------------------------------------------------*/
class Act_Group_Edit extends Page{

    public function __construct(){
        parent::__construct();

        $this->input = trimArr($this->input);
    }

    public function process(){
    	$db = Db::getInstance();
    	$id = (int)$this->input['id'];
    	
    	if($this->input['do'] == 'added'){
    		$req = $this->input['data'];
    		if(!is_array($req)||!$req)return;
    		if(!$req['name'])return $this->alert('请输入用户组名称');
    		if(!$req['menu'])return $this->alert('请给予一点权限');
    		$db ->query("insert into base_admin_user_group (name, menu) values('{$req['name']}','".implode(',',$req['menu'])."')");
    		Admin::redirect(Admin::url('group_list', '', '', true));
    		return;
    	}elseif ($this->input['do'] == 'edited'){
    		$req = $this->input['data'];
    		if(!is_array($req)||!$req || $req['id'] < 1)return;
    		if(!$req['name'])return $this->alert('请输入用户组名称');
    		if(!$req['menu'])return $this->alert('请给予一点权限');
    		$db ->query("update base_admin_user_group set name = '{$req['name']}',menu='".implode(',',$req['menu'])."' where id={$req['id']}");
    		Admin::redirect(Admin::url('group_list', '', '', true));
    		return;
    	}
    	
    	if($this->input['do'] == 'edit' && $id > 0)
    	{
    		$data = $db -> getRow("select * from base_admin_user_group where id = {$id}");
    		$data['menu'] = explode(',', $data['menu']);
    	}else{
    		$data = array();
    	}
		
        $this->assign('data', $data);
        $this->assign('goback', Admin::url('group_list', '', '', true));
        $this->display();
    }
    
    private function alert($msg)
    {
    	return "<script>alert('{$msg}');history.back();</script>";
    }

}
