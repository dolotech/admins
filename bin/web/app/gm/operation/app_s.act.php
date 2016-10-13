<?php
class Act_App_s extends Page{     //记得改这里

	private
        $dbh,
        $db;
	public
	$platformid,
	$serverid;
	public function __construct(){
		parent::__construct();

		$this->setPlatformidServerid();
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->db = Db::getInstance();
		$this->assign('platformid', $this->platformid);
		$this->assign('serverid', $this->serverid);
	}

	public function process(){
          //dump($_SESSION['admin_group_id']);
        if(($this->input['do'])&&($_SESSION['admin_group_id']!=0)){
            echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
            echo "<script>alert('你没有权限操作');window.location.href='?mod=operation&act=app_s';</script>";
              exit; 
        }

        $id = $this->input['id'];
        if($this->input['do'] == 'change_operation0'){
        $sql_up1 = " update serv_apply set state = '1',operation = '1' where id = $id ";
        $this->db->query($sql_up1);
        }

        if($this->input['do'] == 'change_operation1'){
        $sql_up2 = " update serv_apply set state = '2',operation = '2' where id = $id ";
        $this->db->query($sql_up2);
        }

        if($this->input['do'] == 'change_operation2'){
        $sql_up3 = " update serv_apply set state = '3',operation = '3' where id = $id ";
        $this->db->query($sql_up3);
        }

        if($this->input['do'] == 'del'){
        $sql_del = "delete from serv_apply where id = $id ";
        $this->db->query($sql_del);
        }

        $state_type = array(
        '0' => '审核中',
        '1' => '审核通过',
        '2' => '部署中',
        '3' => '部署完成',
        );
        $operation_type = array(
        '0' => '通过审核',
        '1' => '开始部署',
        '2' => '完成部署',
        '3' => '测试',
        );
        $sql = "select * from serv_apply order by id desc ";
        $data = $this->db->getAll($sql); 
        //dump(Game::platform('tencent'));
        //dump($data);exit;
        $this->assign('state_type', $state_type);
        $this->assign('operation_type', $operation_type);
        $this->assign('data', $data);
		$this->display();
	}





}
