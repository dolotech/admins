<?php

/* -----------------------------------------------------+
 * 导入/生成数据
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */


class Act_Gen_code extends Page {

    private $dbh;

    public function __construct(){
        parent::__construct();
        $this->dbh = Db::getInstance();
    }

    public function process()
    {//dump($this->input);
    	//dump($_FILES);
    	//exit;
        if(isset($this->input['submit_type'])
            && $this->input['submit_type'] == 'import_items_txt'
            && $this->input['file']['name'] == 'items.txt'
            && $this->input['file']['error'] == '0'
        ){
            $this->import_items_txt();#<<<<<<<<<<<<<
        }elseif(isset($this->input['submit_type'])
            && $this->input['submit_type'] == 'reward_center_txt'
            && $this->input['file2']['name'] == 'reward_center.txt'
            && $this->input['file2']['error'] == '0'
        ){
            $this->reward_center_txt();
        }elseif(isset($this->input['submit_type'])
            && $this->input['submit_type'] == 'data_item_js'
        ){
            $this->gen_data_item_js();
        }elseif(isset($this->input['submit_type'])
            && $this->input['submit_type'] == 'data_item2_js'
        ){
            $this->gen_data_item2_js();
        }elseif(isset($this->input['submit_type'])
            && $this->input['submit_type'] == 'log_type_js'
        ){
            $this->gen_log_type_js();
        }#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // 清除模板
        $this->clearTemplate();
        // 手动添加要显示的模板
        $this->addTemplate('gen_code');
        $this->display();
    }

    // 导入items.txt数据到数据库
    private function import_items_txt(){
        $fileContent = file_get_contents($this->input['file']['tmp_name']);//把整个文件读入一个字符串中
        $data = explode("\r\n", $fileContent);//根据\r\n来分割字符串保存到数组里,每一行都用一个递增的数字key保存，从0开始
        unset($data[0]);//删除中文标题
        unset($data[1]);//删除英文标题
        // 清空log_goods表
        $this->dbh->exec("TRUNCATE TABLE `log_goods`");
        $time = TIMESTAMP;//获取当前时间戳
        $sql = "insert into log_goods(goodsid, goods_name, goods_ext, goods_time) ";
        //dump($data);
        foreach ($data as $rowString) {
        	
            $row = explode("\t", $rowString);//用table键分割
           //dump($row);
           
            // 第五列的数据格式：{元宝,礼金,银两}
            // 把第五列的字符串转化为PHP数组并且系列化后存数据库
            $row[5] = str_replace(array('{', '}'), '', $row[5]);//把数据去掉{}，用空代替
            //var_dump($row[5]);
            $ext = serialize(explode(',', $row[5]));//去掉逗号并序列化成字符串方便存储且可以转换回数组
            $sqlTail = "values('{$row[0]}', '{$row[1]}', '{$ext}', '{$time}');";
            $this->dbh->exec($sql.$sqlTail);
        }
        $this->assign('alert', '成功导入item.txt数据!');
    }
  
    //生成reward_center.js和reward_center.php 用在奖励中心日志
    private function reward_center_txt(){
    	$fileContent = file_get_contents($this->input['file2']['tmp_name']);//把整个文件读入一个字符串中
    	$data = explode("\r\n", $fileContent);//根据\r\n来分割字符串保存到数组里,每一行都用一个递增的数字key保存，从0开始
    	unset($data[0]);//删除中文标题
    	unset($data[1]);//删除英文标题
    	//dump($data);
    	foreach ($data as $v){
    		if($v){
    		$row = explode("\t", $v);
    		//dump($row);
    		$arr[]="'{$row[0]}'=>'{$row[1]}'";//生成PHP数组 	
    		//dump($arr);
    		$con[]="'{$row[0]}':{'name':'{$row[1]}'}";
    		}

    	}
    	$con=implode(",\r\n", $con);//把数组转化为字符串并用,\n连接
    	$arr=implode(",\r\n", $arr);
    	//dump($con);
    	//dump($arr);
    	$con = "var reward_center = {".$con."};";
    	$fileName = WEB_DIR.'/gm/assets/js/reward_center.js';
    	file_put_contents($fileName, $con);//把数据写入一个文件
    	$this->assign('alert', '成功生成文件：'.$fileName);
    	
    	$arr = "<?php return array(".$arr.");?>";
    	$fileName2 = WEB_DIR.'/gm/assets/js/reward_center.php';
    	file_put_contents($fileName2, $arr);//把数据写入一个文件
    	$this->assign('alert2', '和'.$fileName2);
    }       

    // 生成data_items.js文件
    private function gen_data_item_js(){
        $data = $this->dbh->getAll('select goodsid as id, goods_name as name from `log_goods`');
        $this->assign('data', $data);
        $this->clearTemplate();//清除前面模板内容
        $this->addTemplate('gen_code_data_item_js');//添加模板
        $content = $this->fetch();//获得模板内容，前面加echo的话相当于display（）
        $fileName = WEB_DIR.'/gm/assets/js/data_items.js';
        file_put_contents($fileName, $content);//把数据写入一个文件
        $this->assign('alert', '成功生成文件：'.$fileName);
    }

    // 生成data_items2.js文件
    private function gen_data_item2_js(){
        $data = $this->dbh->getAll('select goodsid as id, goods_name as name, goods_ext as ext from `log_goods`');
        $data2 = array();
        foreach ($data as $row){
            $ext = unserialize($row['ext']);
            //dump($ext);
            $extArr = array();
            foreach($ext as $t => $v){
                if($v) {
                    $t += 1;
                    $extArr[] = "$t:$v";
                }
            }
            $extStr = '{'.implode(',', $extArr).'}';
            $data2[] = "'{$row['id']}':{'name':'{$row['name']}','ext':{$extStr}}";
        }
        $code = implode(",\r\n", $data2);
        //dump($data2);
        $code = "var data_items2 = {".$code."};";
        $fileName = WEB_DIR.'/gm/assets/js/data_items2.js';
       
        //echo $code;
        file_put_contents($fileName, $code);
        $this->assign('alert', '成功生成文件：'.$fileName);
    }
 
    // 生成log_type.js文件 用在银两消费计划
    private function gen_log_type_js(){

    	$log_type = include(APP_DIR.'/log_type.cfg.php');
    	foreach ($log_type as $k =>$v){
    	$data3[]= "'$k':{'name':'$v'}";    	
    	}
    	//echo $log_type[1];
    	//dump($data3);
    	$log_type = implode(",\r\n", $data3);//把数组转化为字符串并用,\n连接
    	$log_type = "var log_type = {".$log_type."};";
    	$fileName = WEB_DIR.'/gm/assets/js/log_type.js';
    	//dump($log_type);
    	file_put_contents($fileName, "$log_type");//把数据写入一个文件
    	$this->assign('alert', '成功生成文件：'.$fileName);
    }

}
