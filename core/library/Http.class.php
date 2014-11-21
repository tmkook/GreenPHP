<?php

/**
 * 发送HTTP网络请求类
 *
 * @version 3.0.0
 * @author moredoo
 */
class Http
{
	/**
	 * 执行一个 HTTP 请求
	 *
	 * @param string $url 执行请求的URL 
	 * @param mixed	$params 表单参数可以是array, 也可以是经过url编码之后的string
	 * @param mixed	$cookie cookie参数可以是array, 也可以是经过拼接的string
	 * @param string $method 请求方法 post / get
	 * @param string $protocol http协议类型 http / https
	 * @return array 结果数组
	 */
	public static function request($query_url, $data = '',$cookie='',$method='post', $is_url = true, $timeout = 30) {
		$url = parse_url($query_url);
		$url['path'] = empty($url['path'])? '/' : $url['path'];
		//$url['port'] = empty($url['port'])? 80  : $url['port'];
		//$url['host'] = $is_url? $url['host'] : gethostbyname($url['host']);
		//open connection
		$ch = curl_init();
		if(!empty($data)){
			$data = self::makeQueryString($data);
		}
		if($method == 'post') {
			curl_setopt($ch,CURLOPT_URL,$query_url);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}else{
			$url['query'] = empty($url['query'])? $data : $url['query'].'%26'.$data;
			$url['path'] .= (isset($url['query'])? '?' . $url['query'] : '') . (isset($url['fragment'])? '#' . $url['fragment'] : '');
			$query_url = $url['scheme'].'://'.$url['host'].$url['path'];
			curl_setopt($ch,CURLOPT_URL,$query_url);
		}
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		//变量初始化
		if(!empty($cookie)){
			$cookie =  self::makeQueryString($cookie);
			curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		}
		//set the url, number of POST vars, POST data
		if($url['scheme'] == 'https'){
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		if($url['port']){
			curl_setopt($ch,CURLOPT_PORT,$url['port']);
		}
		curl_setopt($ch,CURLOPT_AUTOREFERER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch,CURLOPT_FRESH_CONNECT,true);
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
		//$useragent = !empty($_SERVER['HTTP_USER_AGENT']) ? null : $_SERVER['HTTP_USER_AGENT'];
		//curl_setopt($ch,CURLOPT_USERAGENT,$useragent);
		
		//execute post
		$response = curl_exec($ch);
		//get response code
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//close connection 
		curl_close($ch);
		//return result
		if($response_code == 200) {
			return $response;
		} else {
			return false;
		}
	}
	
	public static function makeQueryString($data){
		if(is_array($data)){
			$data = http_build_query($data,'','&');
		}
		return $data;
	}
}

