<?php
/**
*--------------------------------------------------------------
* 文件缓存
*--------------------------------------------------------------
* 最后修改时间 2012-1-9 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @version $Id$
*--------------------------------------------------------------
*/
class FileLog extends Logs
{
	private $_path;
	private $_suffix  = '.log';
    private $msgs = array();

	public function __construct($dsn){
		if(!is_dir($dsn['path'])){
			trigger_error("日志目录 '{$dsn['path']}' 不存在", E_USER_ERROR);
		}
		if(!is_readable($dsn['path'])){
			trigger_error("日志目录 '{$dsn['path']}' 不可读", E_USER_ERROR);
		}
		if(!is_writable($dsn['path'])){
			trigger_error("日志目录 '{$dsn['path']}' 不可写", E_USER_ERROR);
		}
		$this->_path = realpath($dsn['path']).'/';
		if( ! empty($dsn['domain'])) $this->_path .= $dsn['domain'].'/';
		if( ! is_dir($this->_path)){
			if( ! mkdir($this->_path)){
				trigger_error("缓存域 '{$dsn['domain']}' 创建失败",E_USER_ERROR);
			}
		}
	}

	public function write($msg){
		$filename = $this->_path.date('Y-m-d').$this->_suffix;
		$fp = fopen($filename, 'a+');
		if ($fp) {
			flock($fp, LOCK_EX);
			fwrite($fp, date('H:i:s')." ".$msg."\r\n");
			flock($fp, LOCK_UN);
			fclose($fp);
			return true;
		} else {
			return false;
		}
	}
	
	public function add($msg){
		$this->msgs[] = $msg;
        return true;
	}
	
	public function save(){
		foreach($this->msgs as $msg){
            $this->write($msg);
        }
        return true;
	}
	
	public function flush(){
		$dh = opendir($this->_path);
		while ($file = readdir($dh)) {
			if($file!='.' && $file!='..' && !is_dir($file)) unlink($this->_path.$file);
		}
	}

}