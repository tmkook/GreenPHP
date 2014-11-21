<?php
/**
*--------------------------------------------------------------
* 系统常量管理(将常量转为数组并实现常量读写)
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
#--------------------------------------------------------------
//测试配置内容
$str = "\/**[CACHE_TYPE]**\/
define('CACHE_PATH','/cache/'); //描述
\/**[ROUTE_TYPE]**\/
define('ROUTE_TYPE','/route/simple.php'); //描述";
file_put_contents('define.php',$str); //写入配置文件

$a = new Defined('define.php'); //读取配置文件
$b = $a->write(array('CACHE_PATH'=>'test')); //更新配置项
$b = $a->read('CACHE_TYPE'); //读取配置项
var_dump($b);
---------------------------------------------------------------#
*/
class Defined
{

	protected $path;

    /**
    * 构造函数
    *
    * @param string $path 文件路径
    */
	public function __construct($path){
		if( ! file_exists($path)) throw new Exception('文件不存在');
		if( ! is_readable($path)) throw new Exception('文件不可读');
		$this->path = $path;
	}

	/**
	 * 读取配置文件内容
	 * 
	 * @param  string $readkey 要读取的段落
	 * @return array
	 */
	public function read($readkey='') {
		$read_array = array();
		$file_array = $this->getArray();
		$bracket_array = $this->parseBracket();
        $bracket = '';
		$bracket_key = 0;
		foreach((array)$file_array as $key => $val) {
			$bracket = @(string)$bracket_array[$bracket_key-1];
			if(substr_count($val,'define(',0) == 1) {
				$array_key = (int)$array_key;
				$read_array[$bracket][$array_key] = $this->parseDefine($val);
				$array_key++;
			}else{
				$array_key = 0;
				$bracket_key++;
			}
		}
		return (!empty($bracket_array) && empty($readkey)) ? $read_array : $read_array[$readkey];
	}

	/**
	 * 写入新配置内容
	 *
	 * @return string
	 */
	public function write($post_arr) {
		$file_content = $this->getStr();
		preg_match_all('/(define\([\'].*[\']\s?,\s?[\']?).*[^\']([\']?\);\/\/.*)/i',$file_content,$matchs);
		$file_array = $this->getArray();
		$bracket_arr = $this->parseBracket();
        $string = '';
        foreach($file_array as $key=>$val){
            $define = $this->parseDefine($val);
            if(!empty($define) && isset($post_arr[$define['key']])){
                $define['val'] = $post_arr[$define['key']];
                $string .= "define('{$define['key']}','{$define['val']}');//{$define['var']}\r\n";
            }else{
                $string .= $val."\r\n";
            }
        }
        //echo $string;exit;
        return file_put_contents($this->path,"<?php\r\n".$string);
	}
	
	
	protected function getStr(){
		$content = file_get_contents($this->path);
		$content = trim($content);
		$content = substr($content, 0, 5) == '<?php' ? substr($content, 5) : $content;
		$content = substr($content, -2) == '?>' ? substr($content, 0, -2) : $content;
		return $content;
	}
	

	/**
	 * 读取配置文件
	 * 
	 */
	protected function getArray() {
		$content = $this->getStr();
		$array = explode("\r\n",$content);
		$array = array_filter($array);
		foreach($array as $key => $val) {
			if(strlen($val) < 6) {
				unset($array[$key]);
			}
		}
		return $array;
	}
		
	/**
	 * 解析define内容
	 * 
	 * @param string $string 字符串
	 * @return string
	 */
	protected function parseDefine($string) {
		preg_match('/define\([\'](.*)[\']\s?,\s?[\']?(.*[^\'])[\']?\);\/\/(.*)/i',$string,$matchs);
		$array = array();
		if(is_array($matchs) && !empty($matchs)) {
			list(,$tmp_key,$tmp_val,$tmp_var) = $matchs;
			$array = array('key'=>$tmp_key,'val'=>$tmp_val,'var'=>$tmp_var);
		}
		return $array;
	}

	/**
	* 解析区块内容
	* 
	* @return string
	*/
	protected function parseBracket() {
		$content = $this->getStr();
		preg_match_all('/\/\*\*\[(.*)\]\*\*\//i',$content,$matchs);
		if(is_array($matchs) && !empty($matchs)) {
			return $matchs[1];
		}
	}
}
