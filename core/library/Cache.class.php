<?php
/**
*--------------------------------------------------------------
* 缓存抽象工厂
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
*--------------------------------------------------------------
* $dsn = array(
*    'driver'=>'filecache',
*    'domain'=>'_test',
*    'expire'=>3600,
*  ); //根据使用的驱动传入配置参数
* $ch = cache::factory($dsn);
* $ch->set('key','value',1800);
* $ch->get('key');
*--------------------------------------------------------------
*/
abstract class Cache
{
	public static function connect($dsn){
        static $handles = array();
        $key = md5(serialize($dsn));
        if(!isset($handles[$key])){
            $driver = $dsn['driver'];
            $df = dirname(__FILE__).'/cache/'.$driver.'.class.php';
            require_once $df;
            $handles[$key] = new $driver($dsn);
        }
		return $handles[$key];
	}
    
	abstract function __construct($dsn);
	abstract function set($key,$value,$expire=0);
	abstract function get($key);
	abstract function del($key);
	abstract function flush();
}
