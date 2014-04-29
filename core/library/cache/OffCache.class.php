<?php
/**
*--------------------------------------------------------------
* 关闭缓存
*--------------------------------------------------------------
* 最后修改时间 2012-1-9 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @version $Id$
*--------------------------------------------------------------
*/
class offCache extends Cache
{
	function __construct($dsn){
    
    }
	
    function set($key,$value,$expire=0){
        return true;
    }
    
	function get($key){
        return null;
    }
	
    function del($key){
        return true;
    }
	
    function flush(){
        return true;
    }
}
