<?php
/**
*--------------------------------------------------------------
* 日志抽象工厂
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
*--------------------------------------------------------------
* $dsn = array(
*   'driver' => 'file_log', //缓存驱动
*   'path'   => dirname(__FILE__).'/', //缓存存放位置
*   'domain' => 'logs', //缓存目录
* );
* $log = log::factory($dsn);
* $log->write('testing...');
*--------------------------------------------------------------
*/
abstract class Logs
{
	
	public static function connect($dsn){
	    static $handles = array();
        $key = md5(serialize($dsn));
		if(!isset($handles[$key])){
			$driver = $dsn['driver'];
			$df = dirname(__FILE__).'/logs/'.$driver.'.class.php';
			//如果缓存驱动不存在
			if( ! file_exists($df)){
				throw new Exception("日志驱动 '{$driver}' 不存在");
			}
			require_once $df;
			if( ! class_exists($driver)){
				throw new Exception("日志驱动类 '{$driver}' 不存在");
			}
			$handles[$key] = new $driver($dsn);
		}
		return $handles[$key];
	}
	
	abstract public function write($msg);
	abstract public function add($msg);
	abstract public function save();
    abstract public function flush();

}
