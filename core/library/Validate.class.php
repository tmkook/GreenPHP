<?php
/**
*--------------------------------------------------------------
* 数据验证类
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
#--------------------------------------------------------------
$vali = array(
	array(
		'field'=>'username',
		'label'=>'帐号',
		'rules'=> array('required','minlen'=>6,'maxlen'=>16)
	),
);
$validate = new validate();
$_POST['username'] = 'abc';
var_dump($validate->run($_POST,$vali)); //验证一组
echo '<br>';
var_dump($validate->getError()); //获取错误信息
---------------------------------------------------------------#
*/
class Validate
{

	protected $_lang = array();
	protected $_labels = array();
	protected $_roles = array();
	protected $_errors = array();
	
	public function __construct($lang = 'zh_cn'){
		$this->_lang = include 'validate/'.$lang.'.lang.php';
	}
	
	//验证一组数据
	public function run($post,$fields){
		$this->_init($fields);
        $rst = true;
		foreach($this->_roles as $field=>$roles){
			
			foreach($roles as $key=>$match){
				
				//如果不是必填项则跳出验证
				if( ! in_array('required',$roles) && empty($post[$field])) continue;
				
				//开始验证字段
				if(is_numeric($key)){
                    if(!isset($post[$field])) $post[$field] = '';
					$result = $this->$match($post[$field]);
					if( ! $result){
                        $rst = false;
						$this->_errors[$field][] = sprintf($this->_lang[$match],$this->_labels[$field]);
					}
				}else{
					if($key=='eq'){
						$result = $this->eq($post[$field],$post[$match]);
						if( ! $result){
                            $rst = false;
							$this->_errors[$field][] = sprintf($this->_lang['eq'],$this->_labels[$field]);
						}
					}else{
						$result = $this->$key($post[$field],$match);
						if( ! $result){
                            $rst = false;
							$this->_errors[$field][] = sprintf($this->_lang[$key],$this->_labels[$field],$match);
						}
					}
				}
			}
			
		}
        
		return $rst;
	}
	
	//初始化匹配条件
	public function _init($fields){
		foreach($fields as $field){
			$this->_labels[$field['field']] = $field['label'];
			$this->_roles[$field['field']] = $field['rules'];
		}
	}

	//获取错误信息
    public function getMessage(){
		$errors = array_values($this->_errors);
		if(!empty($errors)){
			$errors = $errors[0];
			if(!empty($errors)){
				return $errors[0];
			}
		}
		return '';
	}
	
	//获取全部错误
    public function getError($field = ''){
		if($field==''){
			return $this->_errors;
		}else{
			return $this->_errors[$field];
		}
	}
	
	/**
	* 必填项
	*/
	public function required($data){
		return isset($data);
	}
	
	//二个字段相等
	public function eq($data1,$data2){
		return ($data1==$data2);
	}

	/**
	* 是否是手机
	*/
	public function phone($phone){
		return preg_match('/^(13|15|18)\d{9}$/',$phone);
	}

	/**
	* 是否是邮箱
	*
	* @parame string $email
	*/
	public function email($email){
		return preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email);
	}
	
	/**
	* 是否是链接
	*
	* @parame string $link
	*/
	public function url($url){
		return preg_match('/(http|https){1}:\/\/\.+/', $url);
	}
	
	/**
	* 长度是否超过限制
	*
	* @parame string $data 要检查的数据
	* @parame intger $len 最大范围
	*/
	public function maxlen($data,$len){
		return (strlen($data) <= $len);
	}
	
	/**
	* 长度是否小于最小范围
	*
	* @parame string $data 要检查的数据
	* @parame intger $len 最小范围
	*/
	public function minlen($data,$len){
		return (strlen($data) >= $len);
	}
	
	public function exactlen($data,$len){
		return (strlen($data) == $len);
	}
	
	/**
	* 是否是安全字符(只包含a-zA-Z0-9_)
	*
	* @parame string $data
	*/
	public function safe($data){
		return ! preg_match('/\W/',$data);
	}

    /**
	* 非数字开头安全字符(只包含a-zA-Z0-9_)
	*
	* @parame string $data
	*/
    public function str($data){
        $rst = preg_match('/\W/',$data);
        if( ! $rst){
           $rst = ! is_numeric(substr($data,0));
        }
        return $rst;
    }
	
	public function numeric($data){
		return is_numeric($data);
	}
	
	public function integer($data){
		return (is_numeric($data) && ! strpos($data,'.')/* && $data < 2147483648*/);
	}

	/**
	* 是否是合法的IP格式
	*
	* @parame string $ip
	*/
	public function ip($ip){
		$ip = explode('.',$ip);
		if(count($ip) < 4) return FALSE;
		foreach($ip as $i){
			if($i < 1 || $i > 255) return FALSE;
		}
		return TRUE;
	}

	/**
	* 检查域名DNS
	* 如果域名不可访问则返回FALSE
	*
	* @parame string $host 要检查的域名
	* @parame string $type 类型
	*/
	public function mx($host, $type = 'MX'){
		if(empty($host)) return FALSE;
		exec("nslookup -type={$type} {$host}", $result);
		foreach ($result as $line) {
			if(preg_match("|^{$host}|",$line)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

//end Validate.php
