<?php
/**
*--------------------------------------------------------------
* memcache缓存
*--------------------------------------------------------------
* 最后修改时间 2012-1-9 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @version $Id$
*--------------------------------------------------------------
*/
class MemcacheCache extends Cache
{
	protected $expire = 0;
	protected $domain = '';
	protected $memcache;

	function __construct($dsn){
		if(!empty($dsn['expire'])) $this->expire = $dsn['expire'];
		if(!empty($dsn['domain'])) $this->domain = $dsn['domain'];
		$this->memcache = new Memcache();
		$this->memcache->connect($dsn['path'], $dsn['port']);
	}

	function set($key,$value,$expire=0){
		if($expire==0) $expire = intval($this->expire);
		$this->memcache->set($this->domain.$key,$value,MEMCACHE_COMPRESSED,$expire);
	}

	function get($key){
		return $this->memcache->get($this->domain.$key);
	}

	function del($key){
		$this->memcache->delete($this->domain.$key);
	}

	function flush(){
		$this->memcache->flush();
	}

}
