<?php
class Act_Level extends Page{

    private
        $id,
        $dbh,  //$this->dbh 读取数据库
        $limit = 30,  //每页显示30条
        $page = 0;

    public 
        $platformid,
        $serverid;

    public function __construct(){    //这个页面先执行这个，构造函数，再执行后面的
        parent::__construct();
        if(!isset($this->input['id'])){
            exit("No ID");
        }
        $this->id = $this->input['id'];
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $this->setPlatformidServerid(); 
        //如果input['page']存在且为数字，那么把当前页的$page改变为url的页数
        if(isset($this->input['page']) && is_numeric($this->input['page'])){
            $this->page = $this->input['page'];											//分页			
        }
        //$this->input获得get和post的值,去除首尾空格
        $this->input = trimArr($this->input);
        //连接腾讯服第N个服数据库
        $this->dbh = GameDb::getGameDbInstance($this->platformid, $this->serverid);
        $this->assign('serverid', $this->serverid);
        $this->assign('platformid', $this->platformid);
    }


    //先执行上面的构造函数

    private function getKeyword()
    {
        $kw = array();
        // var_dump($this->input)的结果

        if (($this->input['kw']['reg_st']) && ($this->input['kw']['reg_et']))
        {
            $kw['kw_reg_st'] = strtotime($this->input['kw']['reg_st']);     //获得时间戳
            $kw['kw_reg_et'] = strtotime($this->input['kw']['reg_et']);     //获得保存时间戳的数组
        }		
        return $kw;
    }

    public function process(){
        $kw = $this->getKeyword();	
        $where = " where player_id = '{$this->id}'";

        $where .= isset($kw['kw_reg_st']) && strlen($kw['kw_reg_et']) ?" and time >= '{$kw['kw_reg_st']}' and time <= '{$kw['kw_reg_et']}'":'';		



        $sql = "select * from {$this->dbh->dbname}.log_level ".$where;   //$this->dbh->dbname 读取数据库 名，可以通过更改log_xxx来修改查询的表
        $rs = $this->dbh->selectLimit($sql, $this->page * $this->limit, $this->limit);  //从第x条开始读取$this->limit=30条
        $data = array();					//当前页*每页显示的条数                                        从数据库里读取每页显示的条数

        #var_dump($sql);
        # >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $first_row = true; // 是否为第一条记录
        $first_time = 0;   // 第一次升级的时间
        $last_time = 0;    // 上一次次升级的时间
        while ($row = $this->dbh->fetch_array($rs)) {    //把数据保存到数组里
            if($first_row){
                $row['upgrade_time'] = 0;
                $row['play_time'] = 0;
                $first_row = false; // 设置$first为非第一条
                $first_time = $row['time'];    //获得第一条时间
                $last_time = $row['time'];
            }else{
                $row['upgrade_time'] = $row['time'] - $last_time;    //获得每次升级使用的时间
                $row['play_time'] = $row['time'] - $first_time;     //这里的fist_time永远是第一次拿到的$row[time]的值，计算玩游戏的总时间
                $last_time = $row['time'];   //获得这一次升级的时间，给下一次循环使用
            }
            $data[] = $row;       //把每条信息放进数组里
        }
        //var_dump($data);
        # <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<


        $totalRecord = $this->dbh->getOne("SELECT COUNT(*) FROM {$this->dbh->dbname}.log_level".$where);        //分页
        $pages = Utils::pager(Admin::url('', '', array('id' => $this->id), true), $totalRecord, $this->page,$this->limit);	 //分页
        $this->assign('pages', $pages);							//总条数              //当前页数                      //设置右下方显示的1/xxx页
        $this->assign('data', $data);
        $this->assign('kw', $kw);      //为了搜索后保留搜索关键字在搜索框里
        //$this->assign('data', $this->dbh);
        $this->display();



        #var_dump($where);
    }



}


