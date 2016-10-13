<?php
class Act_Send_lotsize extends Page{     //记得改这里

	public
	$platformid,
	$serverid;	
	
	public function __construct(){
		parent::__construct();
		
		$this->setPlatformidServerid();
		$this->input = trimArr($this->input);
		$this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
		
		$this->assign('serverid', $this->serverid);
		$this->assign('platformid', $this->platformid);
		
		
		if($this->input['data']['order_field1']) $this->orderField1 = $this->input['data']['order_field1'];
		$orderFields1 = array(
				'1' => '发送物品',


		);
		
		
		
		if($this->input['data']['order_field2']) $this->orderField2 = $this->input['data']['order_field2'];
		$orderFields2 = array(
                    '901' => '维护补偿',
                    '902' => 'BUG补偿',
                    '903' => '活动异常补偿',
                    '904' => '奖励补发',
                    '905' => '充值补发',
                    '906' => '活动奖励',
                    '907' => '帮派奖励',
                    '908' => '论坛活动奖励',
                    '999' => '后台奖励',
		);
		//生成下拉框表单
		$this->assign('orderFields1', Form::select('data[order_field1]', $orderFields1, $this->orderField1, false, ' class="am-input-sm" '));
		$this->assign('orderFields2', Form::select('data[order_field2]', $orderFields2, $this->orderField2, false, ' class="am-input-sm" '));		
	

	}
	public function process() {

		$data = $this->getKeyword (); // 注意这里
		if (($this->input ['data'] ['lotsize_num']) 
				&& ($this->input ['data'] ['level_start']) 
				&& ($this->input ['data'] ['level_end'])) {
			$msg = "gmrewardbat|$this->serverid|{\"goods\":{\"{$data['data_lotsize_id']}\":\"{$data['data_lotsize_num']}\"}}"; // 按照协议格式发送消息给服务器
			$msg .= "|{$data['data_level_start']}|{$data['data_level_end']}|{$data['data_login_time']}|{$data['data_order_field2']}"; // 按照协议格式发送消息给服务器
			                                                                                                                                                                                                    // echo $msg;
			$rt = GmAction::send ( $msg, $this->serverid, $this->platformid );
			$url = Admin::url ( 'send_lotsize', 'setting'); // 指定页面的地址
			//echo $url;
			//var_dump($this->input);
			if ($rt ['ret'] == 0) {
				echo "<script>alert('{$rt['msg']}');window.location.href='$url';</script>";
			}
		}

		$this->display ();
	}

	private function getKeyword()
	{
		$data = array();
	
		if (($this->input['data']['lotsize_id']))
		{
			$data['data_lotsize_id'] = $this->input['data']['lotsize_id'];
		}
		if (($this->input['data']['lotsize_num']))
		{
			$data['data_lotsize_num'] = $this->input['data']['lotsize_num'];
		}
		if (($this->input['data']['level_start']))
		{
			$data['data_level_start'] = $this->input['data']['level_start'];
		}
		if (($this->input['data']['level_end']) )
		{
			$data['data_level_end'] = $this->input['data']['level_end'];
		}
		
		if (($this->input['data']['login_time']) )
		{
			$data['data_login_time'] = strtotime($this->input['data']['login_time']);
		}
		
		if (($this->input['data']['order_field1']) )
		{
			$data['data_order_field1'] = $this->input['data']['order_field1'];
		}
		
		if (($this->input['data']['order_field2']) )
		{
			$data['data_order_field2'] = $this->input['data']['order_field2'];
		}
		
		return $data;
	}
	
	
	
	
	
}