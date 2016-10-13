<?php

class MySvn
{
    public $info = '';

	public function __construct($username = 'myserver', $password = '01')
	{
        svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, $username);
        svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $password);
	}

    public function update($path)
    {
        $v = svn_update($path);
        $this->info .= '<br/>更新到版本：'.$v;
    }

    public function commit($log, $path)
    {
        $status = svn_status($path);
        $commit_data = array();
        foreach($status as $sta){
            switch($sta['text_status']){
            case SVN_WC_STATUS_UNVERSIONED :
                if(svn_add($sta['path'])){
                    $commit_data[] = $sta['path'];
                    $this->info .= '<br/>提交新文件：'.$sta['path'];
                }else{
                    $this->info .= '<br/>新增文件失败:'.$sta['path'];
                }
                break;
            case SVN_WC_STATUS_MODIFIED :
                $commit_data[] = $sta['path'];
                $this->info .= '<br/>提交修订版：'.$sta['path'];
                break;
            default:
                $this->info .= '<br/>undef status:'.$sta['text_status'];
            }
        }
        $result = svn_commit($log, $commit_data);
        $this->commit_data = $commit_data;
        if(is_array($result)){
            $this->info .= '<br/>提交后版本：'.$result[0];
        }else{
            $this->info .= '<br/>提交失败！';
        }
    }
}

