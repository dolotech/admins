<?php
/*-----------------------------------------------------+
 * 微信消息处理
 * @author Rolong<rolong@vip.qq.com>
 +-----------------------------------------------------*/

class Weixing
{
    var $postStr;
    var $postObj;
    var $fromUserName;
    var $toUsername;
    var $keyword;
    var $token;
 
    public function __construct($token)
    {
        $this->token = $token;
        if(!$this->checkSignature()) {
            $this->_error('Signature Error');
        }
        //get post data, May be due to the different environments
        $this->postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if( empty($this->postStr) ) {
            $this->_error('Data Error', true);
        }
        $this->postObj = simplexml_load_string($this->postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->fromUsername = $this->postObj->FromUserName;
        $this->toUsername = $this->postObj->ToUserName;
        $this->keyword = trim($this->postObj->Content);
        // TODO:TESE CODE
        $this->_log( $this->keyword );
        $this->_log( "REQ:".$this->fromUsername. " To:" . $this->toUsername );
    }

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature, option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
 
    // 验证签名
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];   
        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
 
    private function _error($e_msg = 'error')
    {
        $date = date('Y-m-d H:i:s');
        error_log("$date $e_msg\n", 3, 'wx_error.txt');
        exit;
    }
 
    private function _log($msg = '')
    {
        if(!$msg) return;
        $date = date('Y-m-d H:i:s');
        error_log("$date $msg\n", 3, 'wx_content.txt');
    }
}
