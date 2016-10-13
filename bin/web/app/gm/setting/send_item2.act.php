<?php

/* -----------------------------------------------------+
 * 指定玩家发送物品
 *
 * @author CRX
 +----------------------------------------------------- */

class Act_Send_item2 extends Page {

    private
        $dbh,
        $dblink,
        $limit = 30,
        $page = 0;

    public
    $platformid,
    $serverid;
    
    public function __construct(){
        parent::__construct();
        $this->setPlatformidServerid();
        $this->input = trimArr($this->input);
        $this->dblink = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
        
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
        //dump($this->input);exit;
        if(isset($this->input['item'])
            && isset($this->input['player_id'])
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
        $item_id=$this->input['item']['goods'];
    	$player_id=$this->input['player_id'];//这里可以是ID也可以是名字
    	$arr_id = explode(",", $player_id);//获得玩家ID或名字数组
        $items = $this->input['item'];
        if(isset($items['goods'])){
            foreach($items['goods'] as $k => $v){
                unset($items['goods'][$k]);
                $items['goods'][$v['id']] = $v['num'];
            }
        }
        // 写日志
        $data = $this->input['data'];
        /*
        $data['login_time'] = strtotime($data['login_time']);
        $data['ctime'] = TIMESTAMP;
        $data['servers'] = serialize($this->input['servers']);
        $data['data'] = serialize($items);
        $data['admin_name'] = $_SESSION['admin_name'];
        $sql = Db::getInsertSql('log_send_item', $data);
        $this->dbh->exec($sql);
        */
        // 发送消息
        $json = json_encode($items);
        // gmrewardbat|服务器ID|json_data|等级开始|等级结束|登陆时间|日志类型
        $ok = true;
        
//判断输入内容是否有错，如果有错则不发送
        foreach ($arr_id as $id){
        	$sql1 = "select player_id from {$this->dblink->dbname}.log_info where player_id = '{$id}'";
        	$sql2 = "select player_id from {$this->dblink->dbname}.log_info where name = '{$id}'";
        	$rs_id1 = $this->dblink->getOne($sql1);//获得玩家ID
        	$rs_id2 = $this->dblink->getOne($sql2);
        	if (($id!=$rs_id1)&&($rs_id2=='')){
        		$url = Admin::url ();
        		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        		echo "<script>alert('输入的ID或名字错误');window.location.href='$url';</script>";
        		exit;
        	}
        		} 
//判断物品ID
        if($item_id){
        foreach($item_id as $it){
      $sql3 = "select goodsid from log_goods where goodsid = '{$it['id']}'"; 
      $rs_item = $this->dbh->getOne($sql3);//获得物品ID
        	if ($it['id']!=$rs_item){
        		$url = Admin::url ();
        		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        		echo "<script>alert('输入的物品ID错误');window.location.href='$url';</script>";
        		exit;
        	}
        
        
            }
        } 
            foreach ($arr_id as $id){
        	if (is_numeric($id))
        	{  	$sql = "select player_id from {$this->dblink->dbname}.log_info where player_id = '{$id}'";
				$rs_id = $this->dblink->getOne($sql);//获得玩家ID
				if($id==$rs_id){
              $msg = "gmreward|$rs_id|1|1|$json|0|0|0|1|{$data['log_type']}";
              $rt = GmAction::send($msg, $this->serverid, $this->platformid); 
				}       	
        	}else{
        		$sql = "select player_id from {$this->dblink->dbname}.log_info where name = '{$id}'";
				$rs_id = $this->dblink->getOne($sql);//获得玩家ID
				if($rs_id!=''){
				$msg = "gmreward|$rs_id|1|1|$json|0|0|0|1|{$data['log_type']}";
				$rt = GmAction::send($msg, $this->serverid, $this->platformid);
        		//dump($rs_id);exit;
					}
        	}
        }
        		
				//dump($msg);exit;
				//dump($rt);
                    if($rt['ret']) {
                        echo $rt['msg'];
                        $ok = false;//当$ok为false时执行下面的exit
                    }
         
        $url = Admin::url ();//因为这个网页没有搜索功能，因此地址上不会带参数而造成跳转错误
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
