<?php

/* -----------------------------------------------------+
 * 礼包管理
 *
 * @author Rolong<rolong@vip.qq.com>
 +----------------------------------------------------- */

class Act_Gift extends Page {

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
        $this->setPlatformidServerid(); 
        $this->assign('platform', Game::platform($this->platformid));
        $this->assign('platformid', $this->platformid);
        $this->assign('serverid', $this->serverid);
        $this->input = trimArr($this->input);
        $this->dbh = Db::getInstance();
        if(
            isset($this->input['page'])
            && is_numeric($this->input['page'])
        ){
            $this->page = $this->input['page'];
        }
    }

    // 批量生成激活码
    public function makeCodes($giftid, $num, $platform){
        $num = $num > 10000 ? 10000 : $num;
        $filename = 'Gift'.$giftid.'_'.$num.'_'.date('Ymd_His').'.csv';
        $str = "卡号\n";
        $str = iconv('utf-8', 'gb2312', $str);
        while($num--){
            $code = $this->makeCode($num);
            $str .= $code . "\n";
            $this->dbh->exec("INSERT INTO `log_giftcode_list` (`giftid`, `code`, `platform`) VALUES ('{$giftid}', '{$code}', '{$platform}');");
        }
        ob_clean();
        ob_start();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
    }

    // 生成一个激活码
    public function makeCode($index){
        $bin1 = pack('V1', TIMESTAMP);
        $bin2 = pack('v1', $index);
        $h1 = bin2hex($bin1);
        $h2 = bin2hex($bin2);
        $c1 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
        $c2 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
        $c3 = mt_rand(0, 1) ? chr(mt_rand(97, 122)) : chr(mt_rand(48, 57));
        $code = $c1 . $h1 . $c2 . $h2 . $c3;
        $code = strtoupper($code);
        return $code;
    }

    public function process()
    {
        if(isset($this->input['item'])){
            $items = $this->input['item'];
            if(isset($items['goods'])){
                foreach($items['goods'] as $k => $v){
                    unset($items['goods'][$k]);
                    $items['goods'][$v['id']] = $v['num'];
                }
            }
            $data = array();
            $data['gift_sn'] = $this->input['gift_sn'];
            $data['gift_name'] = $this->input['gift_name'];
            $data['gift_text'] = $this->input['gift_text'];
            $data['gift_packinfo'] = serialize($items);
            $data['gift_type'] = 2;
            $data['gift_addtime'] = TIMESTAMP;
            $data['gift_adduserid'] = 0;
            $sql = Db::getInsertSql('log_giftcode', $data);
            $this->dbh->exec($sql);
        }elseif(isset($this->input['do']) 
            && $this->input['do'] == 'del'
            && $this->input['giftid'] > 0
        ){
            $this->del();
        }elseif(isset($this->input['do']) 
            && $this->input['do'] == 'makecode'
            && $this->input['giftid'] > 0
            && $this->input['code_num'] > 0
            && $this->input['platform'] != '' 
        ){
            $this->makeCodes($this->input['giftid'], 
                $this->input['code_num'], $this->input['platform']);
            exit;
        }
        $data = array();
        $kw = $this->getKeyword();
        $sqlWhere = $this->getSqlWhere($kw);
        $sqlOrder = " order by giftid desc";
        $sql = "select * from log_giftcode";
        $totalRecord = $this->dbh->getOne(preg_replace('|^SELECT.*?FROM|i', 'SELECT COUNT(*) as total FROM', $sql . $sqlWhere));
        $data['list'] = $this->getList($sql . $sqlWhere . $sqlOrder);
        $data['page_index'] = Utils::pager(Admin::url('', '', '', true), $totalRecord, $this->page, $this->limit);
        $this->assign('kw', $kw);
        $this->assign('data', $data);
        $this->display();
    }

    private function del(){
        $id = $this->input['giftid'];
        $this->dbh->exec("delete from log_giftcode where giftid = '{$id}'");
        // 删除礼包时，同时删除相关的激活码
        $this->dbh->exec("delete from log_giftcode_list where giftid = '{$id}'");
        Admin::redirect(Admin::url());
        exit;
    }

    public function getGiftCount($giftid) {
        $used = $this->dbh->getOne("SELECT count(*) FROM `log_giftcode_list` WHERE giftid={$giftid} and code_use=1 and platform = '{$this->platformid}'");
        $all = $this->dbh->getOne("SELECT count(*) FROM `log_giftcode_list` WHERE giftid={$giftid} and platform = '{$this->platformid}'");
        return array('used' => $used, 'all' => $all);
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
