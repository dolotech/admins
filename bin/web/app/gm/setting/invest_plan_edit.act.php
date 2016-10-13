<?php
/* -----------------------------------------------------+
 * 游戏公告
 *
 * 公告协议：gmgetinvest|服务器ID
 *
 * @author CRX
 +----------------------------------------------------- */
class Act_Invest_plan_edit extends Page{
	
	public
	$platformid,
	$serverid;
	
    public function __construct(){
        parent::__construct();

        $this->setPlatformidServerid();
        $this->input = trimArr($this->input);
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
         
    }

    public function process(){
    	
	

    	$id = $this->input['id'];
 	
    	if($this->input['do'] == 'edit')
    	{
    		$data = $this->dbh -> getRow("select * from invest_plan_data where id = '{$id}'");
    		//$data['menu'] = explode(',', $data['menu']);
     	//var_dump($this->input);
    	//var_dump($this->input['submit']);
		//var_dump($this->serverid);
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
                if($rt['ret'] == 0){
                	echo "<script>alert('{$rt['msg']}');window.location.href='{$url}';</script>";
                }

    		exit;
    }
    
    }else{
    	$data = array();
    }	
        $this->assign('data', $data);
        $this->assign('goback', Admin::url('invest_plan', '', '', true));
        $this->display();
    }
    

}
