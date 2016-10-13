<?php
/***************************************************************
 * 表单元素生成器
 * 
 * @author erlang6@qq.com 
 ***************************************************************/
class Form{

	/**
	 * 生成"select"表单元素
	 * @param string $name Select的名称
	 * @param array $data 选项内容数据
	 * @param string|int $def 默认的选中项
	 * @param string $addons 附加属性
	 * @return string   HTML字串
	 */
	public static function select($name, $data, $def=null, $disable=false, $addons=''){
		$options = '';
		foreach($data as $k=>$v){
			$s = strlen($def) && ($k == $def)?" selected='selected'":'';
			$options .= "<option value='$k'$s>$v</option>";
		}
		$disable = $disable? " disabled='disabled'" : '';
		return "<select name='$name'$disable $addons>$options</select>";
	}
	
	/**
	 * 生成"checkbox"表单元素
	 * @param string $name checkbox的名称
	 * @param array $data 选项内容数据
	 * @param string|int|array $def 默认的选中项
	 * @param string $addon 附加字串
	 * @return string   HTML字串
	 */
	public static function checkbox($name, $data, $def=null, $addon = null){
		$items = '';
		foreach($data as $k=>$v){
		    if(is_array($def)){
		        $c = in_array($k,$def)?" checked='checked'":'';
		    }else{
		        $c = strlen($def) && ($k == $def)?" checked='checked'":'';
		    }
			$items .= "<input name='$name' type='checkbox' value='$k'$c $addon/>$v ";
		}
		return $items;
	}
	
	/**
	 * 生成text表单元素
	 * @param $name		元素名
	 * @param $data		元素值
	 * @param $option	元素其他属性
	 * @return string
	 */
	public static function text($name,$data='',$option=array()){
	    $addon = ' ';
	    foreach($option as $key=>$val){
	        $addon .= $key."=\"".$val."\" ";
	    }
	    return "<input name='$name' type='text' value='$data'$addon/>";
	}
	/**
	 * 生成textarea表单元素
	 * @param $name		元素名
	 * @param $data		元素值
	 * @param $option	元素其他属性
	 * @return string
	 */
	public static function longtext($name,$data='',$option=array()){
	    $addon = ' ';
	    foreach($option as $key=>$val){
	        $addon .= $key."=\"".$val."\" ";
	    }
	    return "<textarea name=\"$name\"$addon>$data</textarea>";
	}
	
	/**
	 * 生成hidden元素
	 * @param $name
	 * @param $data
	 * @return unknown_type
	 */
	public static function hidden($name,$data=''){
	    return "<input name='$name' type='hidden' value='$data' />";
	}
	
	/**
	 * 生成"radio"表单元素
	 * @param string $name radio的名称
	 * @param array $data 选项内容数据
	 * @param string|int $def 默认的选中项
	 * @return string   HTML字串
	 */
	public static function radio($name, $data, $def=null){
		$items = '';
		foreach($data as $k=>$v){
			$c = strlen($def) && ($k == $def)?" checked='checked'":'';
			$items .= "<input name='$name' type='radio' value='$k'$c />$v ";
		}
		return $items;
	}

    public static function yn($name, $def=null){
        return self::select($name, array(''=>'', 1=>'是', 0=>'否'), $def);
    }

    /**
     * 批处理选择器
     * 参数格式为: array([选项名称], [URL], [提示信息(可选)])
     * 示例：
     *      $btns = array(
     *          array('激活', '?mod=users&act=status&val=1', '你确定要激活所有选中的帐号吗？'),
     *          array('禁用', '?mod=users&act=status&val=0', '你确定要禁用所有选中的帐号吗？')
     *      );
     * @param array $options 可选项
     * @return string HTML字符串
     */
    public static function batchSelector($options){
        $opts = "<option valuel=''>对选中项进行批量处理</option>\n";
        $js = '';
        foreach($options as $k=>$v){
            if('-' == $v){ //分隔符
                $opts .= "<option value='' disabled='disabled'>---------</option>\n";
            }else{
                $k = 'o_'.$k;
                $opts .= "<option value='{$k}'>{$v[0]}</option>\n";
			 if($v[3])
			 {
				$js .= "if('{$k}' == value){".$v[3]."}"; 
			 }else{
				 $js .= $v[2]
						? "if('{$k}' == value){if(confirm('{$v[2]}')){f.action='{$v[1]}'; f.method='post'; f.submit();}}\n"
						: "if('{$k}' == value){f.action='{$v[1]}'; f.method='post'; f.submit();}\n";
			 }
                
            }
        }
		return "<script language='javascript'>
		var f = document.getElementById('mainForm');
		function op(value){
            $js
		}
        </script><select id=\"js_op\" onchange=\"op(this.value);\">\n$opts</select>";
    }
}
