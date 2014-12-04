<?php
/**
*--------------------------------------------------------------
* 异步Http请求
*--------------------------------------------------------------
* 最后修改时间 2012-12-04 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
* 异步请求无返回值
#--------------------------------------------------------------
HttpAsync::add("http://localhost/quque/http.php",array("name"=>'test1'));
HttpAsync::add("http://localhost/quque/http.php",array("name"=>'test2'));
HttpAsync::add("http://localhost/quque/http.php",array("name"=>'test3'));
HttpAsync::run();
---------------------------------------------------------------#
*/
class HttpAsync
{
	protected static $reqs = array(); //请求列表
	
	//异步请求一次
	public static function request($url, $params, $cookie='', $method='post', $protocol='http'){
		self::add($url, $params, $cookie='', $method='post', $protocol='http');
		self::run();
	}
	
	//增加一个异步请求
	public static function add($url, $params, $cookie='', $method='post', $protocol='http'){
		self::$reqs[] = self::makeCh($url, $params, $cookie, $method, $protocol); //创建请求句柄到请求列表
	}
	
	//执行全部Http请求
	public static function run(){
		if(empty(self::$reqs)) return;
		$mh = curl_multi_init();
		foreach(self::$reqs as $key=>$ch){
			curl_multi_add_handle($mh,$ch);
		}
		$running=null;
		do {
			curl_multi_exec($mh,$running);
		}while($running > 0);
		foreach(self::$reqs as $key=>$ch){
			curl_multi_remove_handle($mh, $ch);
		}
		curl_multi_close($mh);
		self::$reqs = array();
	}
	
	//创建一个Http连接句柄
	public static function makeCh($url, $params, $cookie='', $method='post', $protocol='http'){
		$query_string = self::makeQueryString($params);	   
	    $cookie_string = self::makeCookieString($cookie);
	    $ch = curl_init();
	    if ('get' == $method){
		    curl_setopt($ch, CURLOPT_URL, "{$url}?{$query_string}");
	    }else{
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
	    }
        
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

	    if (!empty($cookie_string))
	    {
	    	curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
	    }
	    
	    if ('https' == $protocol)
	    {
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    }
		curl_setopt($ch,CURLOPT_TIMEOUT,1);
		return $ch;
	}

	//创建QueryString
	static public function makeQueryString($params){
		if ( ! is_array($params))
			return $params;
			
		$query_string = array();
	    foreach ($params as $key => $value)
	    {
	        array_push($query_string, rawurlencode($key) . '=' . rawurlencode($value));
	    }
	    $query_string = join('&', $query_string);
	    return $query_string;
	}

	//创建QueryString
	static public function makeCookieString($params){
		if ( ! is_array($params))
			return $params;
			
		$cookie_string = array();
	    foreach ($params as $key => $value)
	    {
	        array_push($cookie_string, $key . '=' . $value);
	    }
	    $cookie_string = join('; ', $cookie_string);
	    return $cookie_string;
	}
}
