<?php
/*-----------------------------------------------------+
 * 激活码验证处理
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

define('APIKEY', '0C0E81ABD72C6C8FE6CF91812FC3EB63');

class Act_Code extends Page{

    private $dbh;

    public function __construct(){
        parent::__construct();
    }

    public function process(){
        $required = array('key', 'uid', 'pid', 'sid', 'code');
        $chk = Utils::check_keys($required, $this->input);
        if(true !== $chk){
            exit('Missing '.$chk);
        }
        $userid = $this->input['uid'];
        $code = $this->input['code'];
        $serverid = $this->input['sid'];
        $platformid = $this->input['pid'];
        if (APIKEY != $this->input['key']) {
            exit('Error KEY');
        }
        $this->dbh = Db::getInstance();
        $gift = $this->checkCode($code, $platformid);
        if (!$gift['giftid']) {
            $socketstr = 'gmgift|0|' . $userid . '|' . json_encode(array());
        }else if($gift['use']){
            $socketstr = 'gmgift|3|' . $userid . '|' . json_encode(array());
        } else {
            $codeinfo = $this->dbh->getRow("SELECT * FROM log_giftcode WHERE giftid='$gift[giftid]'");
            // if ($codeinfo['gift_serverid'] !== $serverid && in_array($codeinfo['gift_type'], array('1', '3')) && ($codeinfo['gift_cpid'] > 0)) {
            //     $socketstr = 'gmgift|0|' . $userid . '|' . json_encode(array());
            // } else {
            // }
            $r = $this->checkUserCode($codeinfo, $gift['giftid'], $userid, $serverid, $platformid);
            if($r){
                $this->dbh->update('log_giftcode_list', array('code_use' => '1', 'code_usetime' => TIMESTAMP), array('code' => $code));
                $this->dbh->update('log_giftcode', array('gift_usecodes' => '+1'), array('giftid' => $gift['giftid']));
                $insertData = array(
                    'code_user' => $userid,
                    'code_giftid' => $gift['giftid'],
                    'serviceid' => $serverid,
                    'cpid' => $codeinfo['gift_cpid'],
                    'code_usetime' => TIMESTAMP,
                    'code' => $code,
                    'platform' => $platformid,
                );
                $this->dbh->insert('log_giftcode_user', $insertData);
                $socketstr = 'gmgift|1|' . $userid . '|' . json_encode(unserialize($codeinfo['gift_packinfo']));
            }else{
                $socketstr = 'gmgift|2|' . $userid . '|' . json_encode(array());
            }
 
        }
        $return = GmAction::send($socketstr, $serverid, $platformid);
        // print_r($socketstr);
        // print_r($serverid);
        // print_r($platformid);
        // print_r($return);
        json_encode($return);
    }

    private function checkCode($code, $platformid) {
        $codeinfo = $this->dbh->getRow("SELECT * FROM log_giftcode_list WHERE code='$code' and platform = '$platformid'");
        if ($codeinfo) {
            return array('giftid' => $codeinfo['giftid'], 'code' => $codeinfo['code'], 'use' => $codeinfo['code_use']);
        } else {
            return array('giftid' => '0', 'code' => '0', 'use' => '0');
        }
    }

    public function checkUserCode($codeinfo, $giftid, $userid, $serverid, $platformid) {
        $where = "code_user = '$userid' AND code_giftid = '$giftid' AND serviceid = '$serverid' and platform = '$platformid'";
        $sql = "SELECT COUNT(*) FROM log_giftcode_user WHERE ".$where;
        $num = $this->dbh->getOne($sql);
        return $num > 0 ? FALSE : TRUE;
    }

}
