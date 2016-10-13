<?php

/* -----------------------------------------------------+
 * 游戏公告
 *
 * 公告协议：notice|服务器ID|公告唯一ID|开始播放时间|结束播放时间|时间间隔|公告类型|公告内容
 * 公告类型：以下类型的一个或多个，如果选择多个类型时，用逗号隔开，如：1,2,3,4
 *
 *           1=右上角公告
 *           2=中间不滚动 
 *           3=聊天框  
 *           4=中间滚动
 *
 * 公告删除协议：delnotice|公告唯一ID
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */


class Act_Announcement extends Page {

    private
        $dbh,
        $limit = 30,
        $page = 0;

    public 
        $platformid,
        $serverid;

    public function __construct(){
        parent::__construct();
        // 设置平台ID和服务器ID：
        // 调用$this->setPlatformidServerid();
        // 会自动设置$platformid属性和$serverid属性
        $admin_name = $_SESSION['admin_name'];
        $ad_pid  = $_SESSION['admin_platforms'];
        //dump($ad_pid);
        $admin_pid = $ad_pid[0];
        //dump($admin_pid);
        $this->setPlatformidServerid(); 
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        $this->assign('admin_name',$admin_name);
        $this->assign('admin_pid',$admin_pid);
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

            //dump($_SESSION['admin_platforms']);//记录当前用户的平台权限，

        if(isset($this->input['servers'])){
            $this->send();
        }
        if(isset($this->input['do']) 
            && $this->input['do'] == 'del'
            && $this->input['id'] > 0
        ){
            $this->del();
        }
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by id desc";
        $sql = "select * from game_notice";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlOrder);
        //dump($data['list']);exit;
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->display();
    }

    private function del(){
        $id = $this->input['id'];
        $serversAll = $this->dbh->getOne("select servers from game_notice where id = '{$id}'");
        //var_dump($serversAll);
        $platforms = explode('|', $serversAll);//把字符串拆分成数组
        foreach($platforms as $platformStr){
            $platform = explode(':', $platformStr);//拆分服务器信息dev:1|local:1,2|mytest:1|tencent:1,2,3,4|test:1,2,3
            $pid = $platform[0];
            $serversStr = $platform[1];
            $servers = explode(',', $serversStr);
            foreach($servers as $sid){
                GmAction::send("delnotice|$id", $sid, $pid);//发送给服务端删除消息
            }
        }
        $this->dbh->exec("delete from game_notice where id = '{$id}'");//删除数据库内容，exec()方法执行sql语句
       Admin::redirect(Admin::url());//跳转到当前页面，去掉url多余的参数,方法url()内可以不写，使用默认值
       exit;
    }

    private function send(){
        $start = strtotime($this->input['time_start']);
        $end = strtotime($this->input['time_end']);
        $gap = $this->input['time_gap'];
        $gapSec = $gap * 60;//设置发送消息时间间隔
        $content = $this->input['content'];//input里的内容如果出现双引号（文本字体和效果多个同时使用时）那么会自动加上\,因为里面的内容很可能放在双引号内使用，因此需要转义
        $types = implode(',', $this->input['type']);//把数组组合成字符串用，隔开  公告显示的位置1,2,3,4
 //     var_dump($this->input['type']);
 //     var_dump($this->input);
        $id = time();
        $serversStr = '';

        $pidid='';
        $g_name=$_SESSION['admin_name'];//当前操作者的名字
        foreach($this->input['servers'] as $pid => $servers){
            $sids = implode(',', $servers);
            //记录平台名
            if($pidid == ''){
            $pidid .= "$pid";
            }else{
            $pidid .= ",$pid";
            }

            if($serversStr == ''){
                $serversStr .= "$pid:$sids";
            }else{
                $serversStr .= "|$pid:$sids";
            }
            foreach($servers as $sid){
            	//var_dump($content);
                $content1 = stripslashes($content);//发送给服务端需要去除\ ，写入数据库不需要去除\
                $msg = "notice|$sid|$id|$start|$end|$gapSec|$types|$content1";
                GmAction::send($msg, $sid, $pid);//把消息发送给服务端
                //echo $msg;
            }
        }
        $sql = "INSERT INTO `game`.`game_notice` (`id`,`pid`,`name`, `servers`, `types`, `time_start`, `time_end`, `time_gap`, `message`) VALUES ('$id','$pidid','$g_name', '$serversStr', '$types', '$start', '$end', '$gap', '$content');";
        $this->dbh->exec($sql);//执行sql语句
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
        if($_SESSION['admin_platforms']==''){
            $sqlWhere = " where 1";
        }else{
            $sqlWhere = " where name = '{$_SESSION['admin_name']}' ";
        }
        return $sqlWhere;
    }

    public function getTypeNames($ids)
    {
        $idaArr = explode(',', $ids);
        $rt = '';
        foreach($idaArr as $id){
            $rt .= '【'.$this->getTypeName($id).'】';
        }
        return $rt;
    }

    public function getTypeName($id)
    {
        switch($id){
        case 1 : return '右上角公告';
        case 2 : return '中间不滚动';
        case 3 : return '聊天框';
        case 4 : return '中间滚动';
        }
    }

}
