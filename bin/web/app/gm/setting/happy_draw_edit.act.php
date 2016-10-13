<?php
/* -----------------------------------------------------+
 * 游戏公告
 *
 * 公告协议：gmgetinvest|服务器ID
 *
 * @author CRX
 +----------------------------------------------------- */
class Act_Happy_draw_edit extends Page{
	
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

    	$id=$this->input['id'];
    	if($this->input['do'] == 'edit')
    	{
    		$data = $this->dbh -> getRow("select * from happy_draw where id = '{$id}'");
		//$data['menu'] = explode(',', $data['menu']);
     	//var_dump($this->input);
    	//var_dump($this->input['submit']);
		//var_dump($this->serverid);
    	if($this->input['submit']){
    		$time_st = strtotime($this->input['time_st']);
    		$time_ed = strtotime($this->input['time_ed']);
    		$this->dbh->query("update happy_draw set start_time = '{$time_st}',end_time = '{$time_ed}' where id ='{$id}'");	
                $msg = "gmdrawhappy|{$this->serverid}";//按照协议格式发送消息给服务器
                $rt = GmAction::send($msg, $this->serverid, $this->platformid);
                $url = Admin::url('happy_draw', '', '', true);//指定页面的地址
                //echo $url;
                if($rt['ret'] == 0){
                	echo "<script>alert('OK');window.location.href='{$url}';</script>";
                }

    		exit;
    }
    
    }else{
    	$data = array();
    }	
        $this->assign('data', $data);
        $this->assign('goback', Admin::url('happy_draw', '', '', true));
        $this->display();
    }
    

}
