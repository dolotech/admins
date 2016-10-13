<?php
/* -----------------------------------------------------+
 * 开服信息
 *
 * 公告协议：gmgetinvest|服务器ID
 *
 * @author CRX
 +----------------------------------------------------- */
class Act_App_s_edit extends Page{
	
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
    	
        $ps = Config::getInstance()->get('platformsServers');
        $pid_arr=array();//构造下拉框数组
        foreach($ps as $pid=>$v){
              $pid_arr[$pid]=Game::platform($pid);
        }
         //dump($pid_arr);
        //dump($ps);
		//生成下拉框表单
		$this->assign('pid_select', Form::select('pid', $pid_arr, $this->input['pid']));

    	$id = $this->input['id'];
    	if($this->input['submit']){
           $pid = $this->input['pid'];
           $server_no = $this->input['server_no'];
           $server_id = $this->input['server_id'];
           $server_name = $this->input['server_name'];
           $open_time = strtotime($this->input['open_time']);
           $note = $this->input['note'];
        $sql = "insert into serv_apply(platform_id,server_no,server_id,server_name,state,operation,open_time,apply_name,note) values('{$pid}','{$server_no}','{$server_id}','{$server_name}','0','0','{$open_time}','{$_SESSION['admin_name']}','{$note}')";
        $this->db->query("$sql");
            echo "<script>alert('ok');window.location.href='?mod=operation&act=app_s';</script>";
        } 
       //---- 
        /*
    	if($this->input['do'] == 'edit')
    	{
    		$data = $this->dbh -> getRow("select * from invest_plan_data where id = '{$id}'");



    	if($this->input['submit']){
    		$start_time = strtotime($this->input['data']['start_time']);
    		$end_time = strtotime($this->input['data']['end_time']);
    		$yb = $this->input['data']['yb'];
    		$prize = $this->input['data']['prize'];
    		$this->dbh->query("update invest_plan_data set start_time = '{$start_time}',end_time = '{$end_time}',yb = '{$yb}' ,prize = '{$prize}' where id='{$id}'");

    		//var_dump("update invest_plan_data set name = '{$data['name']}' where id='{$id}'");
    		
                $msg = "gmgetinvest|{$this->serverid}";//按照协议格式发送消息给服务器
                $rt = GmAction::send($msg, $this->serverid, $this->platformid);
                $url = Admin::url('invest_plan', '', '', true);//指定页面的地址
                //echo $url;
                if($rt['ret'] == 0){
                	echo "<script>alert('{$rt['msg']}');window.location.href='{$url}';</script>";
                }

    		exit;
    }
    
    }else{
    	$data = array();
    }	
         */
        $this->assign('data', $data);
        $this->assign('goback', Admin::url('app_s', '', '', true));
        $this->display();
    }
    

}
