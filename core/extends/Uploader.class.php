<?php
class Uploader
{
	protected $IMG_DIR = '/temp/';
	
	public function __construct(){
		$this->IMG_DIR = dirname(dirname(dirname(__FILE__))).$this->IMG_DIR;
	}
	
	public function uploadImg($string,$filename){
		$img = new Images($string);
		return $img->save($this->IMG_DIR.'img/'.$filename);
	}
	
	public function getImg($filename,$w=0,$h=0){
		if(empty($filename)) $filename = 'default.jpg';
		$path = $this->IMG_DIR.'img/'.$filename;
		$img = new Images($path);
		$img->zoom($w,$h);
		$img->display();
	}
	
	public function delImg($filename){
		if($filename=='default.jpg'){
			throw new Exception("forbid");
		}
		unlink($this->IMG_DIR.'img/'.$filename);
	}
	
	public function uploadFace($string,$filename){
		$img = new Images($string);
		$img->zoom(400);
		//$img->zoom(100,100,'min');
		//$img->crop(100,100,'middle','middle');
		return $img->save($this->IMG_DIR.'face/'.$filename);
	}
	
	public function getFace($filename,$w=0,$h=0){
		if(empty($filename)) $filename = 'face.jpg';
		$path = $this->IMG_DIR.'face/'.$filename;
		$img = new Images($path);
		if($w > 0 && $h > 0){
			$img->zoom($w,$h);
		}
		$img->display();
	}

}