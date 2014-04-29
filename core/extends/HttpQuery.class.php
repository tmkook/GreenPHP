<?php
class HttpQuery
{
	protected $param;
	public function __construct(){
		
	}

	public function get($key){
		return isset($_GET[$key])? $_GET[$key] : '';
	}
	
	public function post($key){
		return isset($_POST[$key])? $_POST[$key] : '';
	}
	
	public function query($key){
		if(isset($_POST[$key])){
			return $_POST[$key];
		}elseif(isset($_GET[$key])){
			return $_GET[$key];
		}else{
			return '';
		}
	}
	
	public function param($key=''){
		static $param;
		$json = $this->query('parameter');
		if(empty($json)) return '';
		$this->param = json_decode($json,true);
		if(empty($key)) return $this->param;
		return isset($this->param[$key])? $this->param[$key] : '';
	}

}