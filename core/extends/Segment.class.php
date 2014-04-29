<?php
class Segment
{
    private static $baseurl = './';
    
    public static function setBaseUrl($baseurl){
        self::$baseurl = $baseurl;
    }
    
	//http://localhost/index.php?m=x&c=x&t=x&test=x
    public static function build($uri){
        //$baseurl = $this->getBaseUrl();
		//return empty($baseurl)? $uri : $baseurl.$uri;
		$build = self::$baseurl;
		$parsed = parse_url($uri);
		$path  = trim($parsed['path'],'/');
		if($path == 'index.php') $path = 'view.php';
		$build .= empty($path)? '' : current(explode('.',$path));
		$uri = array();
		if(!empty($parsed['query'])) parse_str($parsed['query'],$uri);
		$build .= "/{$uri['m']}/{$uri['c']}";
		if(!empty($uri['t'])) $build .= "/{$uri['t']}";
		unset($uri['m'],$uri['c'],$uri['t']);
		if(!empty($uri)){
			$uri = http_build_query($uri);
			$build .= "?{$uri}";
		}
		return $build;
    }
    

    public static function redirect($url){
        header('Location:'.self::build($url));
    }

}

//echo Url::build('index.php?m=x&c=x&&test=x');
