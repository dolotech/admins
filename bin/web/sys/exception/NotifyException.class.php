<?php
/*-----------------------------------------------------+
 * 用户通知
 * @author erlang6@qq.com
 +-----------------------------------------------------*/
class NotifyException extends Exception{
    protected $links;

    /**
     * @param string $message 信息字符串
     * @param int $code 信息代号
     * @param array $links 跳转链接
     * 例:$links = array( '到a页'=>'a.html','到b页' => 'b.html');
     */
    public function __construct($message = null, $code = 0, $links = array()){
        parent::__construct($message, $code);
        $this->message = $message;
        $this->links = isset($links) && is_array($links)? $links : array();
    }

    public function raiseMsg($msg= null) {
        $v= array ();
        $v['msg']= $msg ? $msg : $this->message;
        $v['links'] = '';
        if(count($this->links)) {
            foreach ($this->links as $key => $val) {
                $v['links'] .= '<a href="'.$val.'">'.$key.'</a>&nbsp;';
            }
        }

        include APP_DIR.'/'.APP_ID.'/message.tpl.htm';
    }
}
