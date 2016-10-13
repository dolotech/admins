<?php

/* -----------------------------------------------------+
 * 批量发送物品
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Send_item extends Page {

    private
        $dbh,
        $limit = 30,
        $page = 0;

    public function __construct(){
        parent::__construct();
        $this->input = trimArr($this->input);
        $this->dbh = Db::getInstance();
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }
    }

    public function process()
    {
        if(isset($this->input['item'])
            && isset($this->input['servers'])
            && isset($this->input['data'])
        ){
            $this->send();
        }
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from log_send_item";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $logTypes = Game::rewardTypes();
        //生成下拉框表单
		$this->assign('logTypes', Form::select('data[log_type]', $logTypes, '', false, ' class="am-input-sm" '));
        $this->display();
    }

    /**
     * 发送...
     */
    private function send()
    {
        $items = $this->input['item'];
        if(isset($items['goods'])){
            foreach($items['goods'] as $k => $v){
                unset($items['goods'][$k]);
                $items['goods'][$v['id']] = $v['num'];
            }
        }
        // 写日志
        $data = $this->input['data'];
        $data['login_time'] = strtotime($data['login_time']);
        $data['ctime'] = TIMESTAMP;
        $data['servers'] = serialize($this->input['servers']);
        $data['data'] = serialize($items);
        $data['admin_name'] = $_SESSION['admin_name'];
        $sql = Db::getInsertSql('log_send_item', $data);
        $this->dbh->exec($sql);
        // 发送消息
        $json = json_encode($items);
        // gmrewardbat|服务器ID|json_data|等级开始|等级结束|登陆时间|日志类型
        $msgTail = "$json|{$data['level_start']}|{$data['level_end']}|{$data['login_time']}|{$data['log_type']}";
        $ok = true;
        foreach($this->input['servers'] as $pid => $servers){
            foreach($servers as $sid){
                $msid = Config::getInstance($pid.'_s'.$sid)->get('merge_server_id');
                if($msid){
                    $msid = $sid . ',' . $msid;
                    $sidArr = explode(',', $msid);
                }else{
                    $sidArr = array($sid);
                }
                foreach($sidArr as $s){
                    $msg = "gmrewardbat|$s|".$msgTail;
                    $rt = GmAction::send($msg, $sid, $pid);
                    if($rt['ret']) {
                        echo $rt['msg'];
                        $ok = false;
                    }
                }
            }
        }
        $url = Admin::url ();
        if ($ok) {
            echo "<script>alert('{$rt['msg']}');window.location.href='$url';</script>";
        }
        exit;
    }

    /**
     * 取得搜索关键字
     * @return array
     */
    private function getKeyword()
    {
        $kw = array();
        return $kw;
    }

    /**
     * 获取列表数据
     * @param string $sql SQL查询字串
     * @return array
     */
    private function getList($sql)
    {
        $rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);
        $list = array();
        while ($row = $this->dbh->fetch_array($rs)) {
            $list[] = $row;
        }
        return $list;
    }

    /**
     * 构造SQL where字串
     * @param array $kw 搜索关键字
     */
    private function getSqlWhere($kw)
    {
        $sqlWhere = " where 1";
        return $sqlWhere;
    }

}
