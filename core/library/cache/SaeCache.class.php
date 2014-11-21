<?php
/**
*--------------------------------------------------------------
* 新浪Sae缓存
*--------------------------------------------------------------
* 最后修改时间 2012-1-9 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @version $Id$
*--------------------------------------------------------------
*/
class saeCache extends Cache
{
	protected $sae;
    protected $expire = 0;
	protected $domain = '';

	function __construct($dsn) {
		$this->sae = memcache_init();
        if(!empty($dsn['expire'])) $this->expire = $dsn['expire'];
        if(!empty($dsn['domain'])) $this->domain = $dsn['domain'];
	}

	function get($key) {
		return memcache_get($this->sae, MC_PREFIX.$this->domain.$key);
	}

	function set($key, $value, $expire = 0) {
        if($expire==0) $expire = intval($this->expire);
		return memcache_set($this->sae, MC_PREFIX.$this->domain.$key, $value, MEMCACHE_COMPRESSED, $expire);
	}

	function del($key) {
		return memcache_delete($this->sae, MC_PREFIX.$this->domain.$key);
	}

	function flush() {
		return memcache_flush($this->sae);
	}
}
