<?php
class ApiDocReflect
{
	protected $blocks = array();
	
	public function __construct($block){
		$block = explode("\r\n",$block);
		foreach($block as $bk=>$bv){
			$bv = preg_replace("/\s*\*\s*/",'',$bv);
			if($bv=='/' || empty($bv) || $bv==' '){
				unset($block[$bk]);
			}else{
				$block[$bk] = $bv;
			}
		}
		$this->blocks = array_values($block);
	}
	
	public function getDescr(){
		$parames = array();
		foreach($this->blocks as $bk=>$bv){
			if(substr($bv, 0, 1 ) != '@'){
				$parames[] = $bv;
				unset($this->blocks[$bk]);
			}
		}
		return $parames;
	}

	public function getParams(){
		$parames = array();
		foreach($this->blocks as $bk=>$bv){
			$row = explode(' ',$bv);
			if($row[0]=='@param'){
				$parames[] = array(
					'name'=>$row[1],
					'type'=>$row[2],
					'is_required'=>$row[3],
					'descr'=>$row[4]
				);
				unset($this->blocks[$bk]);
			}
		}
		return $parames;
	}
	
	public function getExample(){
		$parames = array();
		foreach($this->blocks as $bk=>$bv){
			$row = explode(' ',$bv);
			if($row[0]=='@uri'){
				$parames['uri'] = $row[1];
				unset($this->blocks[$bk]);
			}elseif($row[0]=='@request'){
				$parames['request'] = array('type'=>$row[1],'param'=>$row[2]);
				unset($this->blocks[$bk]);
			}elseif($row[0]=='@success'){
				$parames['success'] = array('type'=>$row[1],'param'=>$row[2]);
				unset($this->blocks[$bk]);
			}elseif($row[0]=='@error'){
				$parames['error'][] = array('type'=>$row[1],'param'=>$row[2]);
				unset($this->blocks[$bk]);
			}
		}
		return $parames;
	}
	
	public function getData(){
		$parames = array();
		foreach($this->blocks as $bk=>$bv){
			$row = explode(' ',$bv);
			if($row[0]=='@data'){
				$parames[] = array(
					'name'=>$row[1],
					'descr'=>$row[2],
				);
				unset($this->blocks[$bk]);
			}
		}
		return $parames;
	}

	public function getTag($tag){
		$parames = '';
		foreach($this->blocks as $bk=>$bv){
			$row = explode(' ',$bv);
			if($row[0]==$tag){
				unset($row[0]);
				$row = implode(' ',$row);
				$parames = $row;
				unset($this->blocks[$bk]);
			}
		}
		return $parames;
	}
}