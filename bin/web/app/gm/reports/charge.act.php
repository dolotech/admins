<?php

/* -----------------------------------------------------+
 * 充值统计报表
 * @author CRX
 +----------------------------------------------------- */

class Act_Charge extends Page
{
	private
	$dbh,
	$limit = 30,
	$page = 0;
	
	public
	$platformid,
	$serverid;
	
    public function __construct()
    {
        parent::__construct();
        
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $this->setPlatformidServerid();
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        $this->input = trimArr($this->input);
        $this->dbh = Db::getInstance();//连接默认数据库game
        if(
        		isset($this->input['page'])
        		&& is_numeric($this->input['page'])
        ){
        	$this->page = $this->input['page'];
        }
        

    }

    public function process()
    {
    	//dump($this->input);
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from log_charge_order";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        //var_dump( $data['list']);
        $dataAll = $this->dbh->getAll($sql . $sqlWhere . $sqlOrder);
        // var_dump($dataAll);      
        $this->excel($dataAll);	//调用导出excel方法
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->display();
    }
    /**
     * 取得搜索关键字
     * @return array
     */
    private function getKeyword()
    {
    	$kw = array();

    	if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
    	{
    		$kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);
    		$kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);
    	}
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
    	$sqlWhere = " where platformId = '{$this->platformid}' ";
    	if (isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']))
    	{
    		$sqlWhere .= " and ctime >= '{$kw['kw_reg_st']}' and ctime <= '{$kw['kw_reg_et']}'";
    	}
    	return $sqlWhere;
    }
    
    private function excel($dataexcel){
    	if(isset($this->input['submit_type'])&&$this->input['submit_type']=="excel"){
//     		include '/assets/excel/Classes/PHPExcel.php';
//     		include '/assets/excel/Classes/PHPExcel/Writer/Excel2007.php';
//     		include '/assets/excel/Classes/PHPExcel/Writer/Excel5.php';
    
    		$objPHPExcel=new PHPExcel();
    		$objPHPExcel->getProperties()->setCreator('CRX')
    		->setLastModifiedBy('CRX')
    		->setTitle('Office 2007 XLSX Document')
    		->setSubject('Office 2007 XLSX Document')
    		->setDescription('Document for Office 2007 XLSX, generated using PHP classes.')
    		->setKeywords('office 2007 openxml php')
    		->setCategory('Result file');
    		 
    		$objPHPExcel->setActiveSheetIndex(0)
    		->setCellValue('A1','ID')
    		->setCellValue('B1','账号')
    		->setCellValue('C1','平台')
    		->setCellValue('D1','分区')
    		->setCellValue('E1','订单号')
    		->setCellValue('F1','金额(元)')
    		->setCellValue('G1','元宝')
    		->setCellValue('H1','首充')
    		->setCellValue('I1','发货')
    		->setCellValue('J1','时间');
    		 
    		$i=2;
    		foreach($dataexcel as $v){
    			 
    			$money = $v['money']/100;
    			 
    			if($v['isFirst']){
    				$isFirst = '是';
    			}
    			else {
    				$isFirst =  '否';
    			}
    			 
    			if($v['isVerified']){
    				$isVerified = '已发货';
    			}
    			else {
    				$isVerified = '未发货';
    			}
    			$ctime = date('Y-m-d H:i:s', $v['ctime']);
    			     			
    			$objPHPExcel->setActiveSheetIndex(0)
    			->setCellValue('A'.$i,$v['id'])
    			->setCellValue('B'.$i,$v['account'])
    			->setCellValue('C'.$i,Game::platform($v['platformId']))
    			->setCellValue('D'.$i,$v['serverId'])
    			->setCellValue('E'.$i,$v['myOrderId'])
    			->setCellValue('F'.$i,$money)
    			->setCellValue('G'.$i,$v['moneyGame'])
    			->setCellValue('H'.$i,$isFirst)
    			->setCellValue('I'.$i,$isVerified)
    			->setCellValue('J'.$i,$ctime);
    			$i++;
    		}
    		$objPHPExcel->getActiveSheet()->setTitle('充值统计报表');
    		$objPHPExcel->setActiveSheetIndex(0);
    		//$filename=urlencode('充值统计报表').'_'.date('Ymd');
    		$filename=urlencode('充值统计报表').'_'.date("md-Hi",strtotime($this->input['kw']['reg_st'])).'_'.date("md-Hi",strtotime($this->input['kw']['reg_et']));
    		
    
    		header('Content-Type: application/vnd.ms-excel');
    		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
    		header('Cache-Control: max-age=0');
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    		 
    		 
    		 
    		$objWriter->save('php://output');
    		exit;//结束页面，使前端页面内容不会出现在excel里面
    	}
    }
    
}
