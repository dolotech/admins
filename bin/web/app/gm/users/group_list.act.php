<?php
/*-----------------------------------------------------+
 * 后台帐号列表
 +-----------------------------------------------------*/
class Act_Group_List extends Page{
    private
        $limit = 30,
        $page = 0;

    public function __construct(){
        parent::__construct();

        $this->input = trimArr($this->input);

        if(
            isset($this->input['limit'])
            && is_numeric($this->input['limit'])
            && $this->input['limit'] <= 1000
        ){
            $this->limit = $this->input['limit'];
        }

        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }

        $this->assign('limit', $this->limit);
    }

    public function process(){
    	$db = Db::getInstance();
        $data = array();
        
        if($this->input['do'] == 'del' && $this->input['id'] > 0)
        {
        	$id = (int)$this->input['id'];
        	$db -> query("delete from base_admin_user_group where id = {$id}");
        }

        $sql = "select * from base_admin_user_group";
        $totalRecord = $db->getOne(preg_replace('|^SELECT.*FROM|i', 'SELECT COUNT(*) as total FROM', $sql));
		$list = array();
		$tmp = $db->getAll($sql);
		$menu = Admin::getMenu();
		foreach($tmp as $pval)
		{
			$exp = explode(',', $pval['menu']);
			$m = array();
			foreach($exp as $mid)
			{
				$m[] = $menu[$mid]['title'];
			}
			$pval['menu'] = implode(',', $m);
			$list[] = $pval;
		}
        $data['list'] = $list;
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);

        $this->assign('data', $data);
        $this->assign('formAction', Admin::url('', '', '', true));
        $this->display();
    }

}
