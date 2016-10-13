<?php
/*-----------------------------------------------------+
 * 网站管理入口模块 
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Act_Index extends Page{
    public function __construct(){
        parent::__construct();
    }

    public function process(){
        $data = ChargeStat::getStat();
        $this->assign('data', $data);
        $this->display();
    }
}
