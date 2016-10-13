<?php
/*-----------------------------------------------------+
 * 后台帐号列表
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_List extends Page{
    private
        $dbh,
        $limit = 30,
        $page = 0;

    public function __construct(){
        parent::__construct();

        $this->input = trimArr($this->input);
        $this->dbh = Db::getInstance();

        $this->clearParamCache();

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
        $this->addParamCache(array('limit'=>$this->limit));
    }

    public function process(){
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from base_admin_user";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*FROM|i', 'SELECT COUNT(*) as total FROM', $sql.$sqlWhere));

        $data['list'] = $this->getList($sql.$sqlWhere.$sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);

        $kw['kw_status'] = Form::select('kw_status', array(
            ''=>'',
            '1'=>'已激活',
            '0'=>'未激活'
        ), isset($kw['kw_status']) ? $kw['kw_status'] : '');

        $this->assign('kw', $kw);
        $this->assign('op', $this->op());
        $this->assign('data', $data);
        $this->assign('formAction', Admin::url('', '', '', true));
        $this->display();
    }

    /**
     * 取得搜索关键字
     * @return array
     */
    private function getKeyword(){
        $kw = array();
        if(isset($this->input['kw_name'])){
            $kw['kw_name'] = $this->input['kw_name'];
        }
        if(isset($this->input['kw_username'])){
            $kw['kw_username'] = $this->input['kw_username'];
        }
        if(isset($this->input['kw_status'])){
            $kw['kw_status'] = $this->input['kw_status'];
        }
        if(isset($this->input['kw_last_visit_begin'])){
            $kw['kw_last_visit_begin'] = $this->input['kw_last_visit_begin'];
        }
        if(isset($this->input['kw_last_visit_end'])){
            $kw['kw_last_visit_end'] = $this->input['kw_last_visit_end'];
        }
        $this->addParamCache($kw); //缓存关键字
        return $kw;
    }

    /**
     * 获取列表数据
     * @param string $sql SQL查询字串
     * @return array
     */
    private function getList($sql){
    	$group = Admin::getUserGroup();
        $rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);
//        if(0 != $this->page){ //如果在指定的分页查找不到数据，则读取第一页
//            $this->page = 0;
//            $rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);
//        }
        $this->addParamCache(array('page'=>$this->page));
        
        $list = array();
        while($row = $this->dbh->fetch_array($rs)){
			if(!$row['group_id'] && $_SESSION['admin_group_id']){
				continue;
			}
            $row['status'] = $row['status']? '<span class="am-badge am-badge-success">已激活</span>' : '<span class="am-badge am-badge-success">未激活</span>'; 
            $row['reg_date'] = date('Y-m-d');
            $row['group'] = $group[$row['group_id']]['name'];
            $row['last_visit'] = $row['last_visit'] ? date('Y-m-d H:i:s', $row['last_visit']) : '从未登录';
            $row['last_ip'] = $row['last_ip'] ? $row['last_ip'] : '从未登录';
            $row['last_addr'] = $row['last_addr'] ? $row['last_addr'] : '从未登录';
            $row['action'] = '<a href="'.Admin::url('edit', '', array('id'=>$row['id'])).'">详细</a> <a href="javascript:if(confirm(\'你确定要删除此项吗？\'))location.replace(\''.Admin::url('delete', '', array('id'=>$row['id'])).'\');">删除</a>';

            $list[] = $row;
        }
        return $list;
    }

    /**
     * 构造SQL where字串
     * @param array $kw 搜索关键字
     */
    private function getSqlWhere($kw){
        $sqlWhere=" where 1";
        $sqlWhere .= isset($kw['kw_name']) && strlen($kw['kw_name']) ? " and name like('%{$kw['kw_name']}%')" : '';
        $sqlWhere .= isset($kw['kw_username']) && strlen($kw['kw_username']) ? " and username like('%{$kw['kw_username']}%')" : '';
        $sqlWhere .= isset($kw['kw_status']) && strlen($kw['kw_status']) ? " and status={$kw['kw_status']}" : '';
        $sqlWhere .= isset($kw['kw_last_visit_begin']) && isDate($kw['kw_last_visit_begin']) ? " and last_visit >=".unixtime($kw['kw_last_visit_begin']) : '';
        $sqlWhere .= isset($kw['kw_last_visit_end']) && isDate($kw['kw_last_visit_end']) ? " and last_visit <=".unixtime($kw['kw_last_visit_end'], '23:59:59') : '';

        return $sqlWhere;
    }

    /**
     * 生成批量处理按钮
     */
    private function op(){
        $btns = array(
            array('激活', Admin::url('status', '', array('val'=>1), true), '你确定要激活所有选中的帐号吗？'),
            array('禁用', Admin::url('status', '', array('val'=>0), true), '你确定要禁用所有选中的帐号吗？'),
            '-',
            array('删除', Admin::url('delete', '', '', true), '注意!该操作不可恢复.你确定要删除所有的选中项吗？')
        );
        return Form::batchSelector($btns);
    }
}
