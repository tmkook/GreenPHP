<?php
/*
*--------------------------------------------------------------
* 配置管理,整个系统配置的获取修改与删除等操作
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @version 1.1
* @author Leon(tmkook@gmail.com)
* @date 2010-2-27
*--------------------------------------------------------------
*/
class Config
{
	private static $_config = array();

	private static $_dirs   = array();
	
	static function addPath($dirs){
		self::$_dirs = array_merge(self::$_dirs,(array)$dirs);
	}

    /**
    * 将路径解析成数组
    *
    * @parame $path string 要解析的路径
    * @return array
    */
	static function getKey($path){
		$path = trim($path,'/');
		if(!empty($path)){
			if(strpos($path,'/')){
				$path = explode('/',$path);
			}else{
				$path = (array)$path;
			}
		}
		return $path;
	}

    /**
    * 获取配置项
    *
    * @parame $path string 配置项索引格式:"文件/索引/索引..."
    * @return data
    */
	static function get($path){
		$path = self::getKey($path);

		//没有加载
		if(!isset(self::$_config[$path[0]])){
			foreach(self::$_dirs as $dir){
				$file = $dir.$path[0].'.php';
				if(file_exists($file)){
					self::$_config[$path[0]] = include_once $file;
					break;
				}else{
					$file = FALSE;
				}
			}
			if( ! $file) throw new Exception("不存在的配置文件");
		}

		//返回配置项
		$conf = self::$_config[$path[0]];
		unset($path[0]);
		if(is_array($path) && !empty($path)){
			foreach($path as $key){
				if(!isset($conf[$key])){
					throw new Exception("不存在的配置项");
				}
				$conf = $conf[$key];
			}
		}
		return $conf;
	}

    /**
    * 设置配置项
    *
    * @parame $path string 要修改的配置项索引
    * @parame $value data 索引的值
    * @return 无返回值
    */
	static function set($path,$value){
		$path = self::getKey($path);
		$set  =& self::$_config[$path[0]];
		unset($path[0]);
		if(is_array($path) && !empty($path)){
			foreach($path as $key){
				$set =& $set[$key];
			}
		}
		$set = $value;
	}

    /**
    * 删除配置项
    *
    * @parame $path string 要删除的配置项索引
    * @return 无返回值
    */
	static function remove($path){
		$path = self::getKey($path);
		$set  =& self::$_config[$path[0]];
		unset($path[0]);
		if(is_array($path) && !empty($path)){
			foreach($path as $key){
				$set =& $set[$key];
			}
		}
		$set = NULL;
		unset($set);
	}

    /**
    * 清除所有配置
    *
    * @return 无返回值
    */
    static function clean(){
       unset(self::$_config);
    }

	static function save($file){
		$data = '<?php'."\r\n".'return '.var_export((array)self::$_config[$file],TRUE).';';
		foreach(self::$_dirs as $dir){
			$save_file = $dir.$file.'.php';
			if(file_exists($save_file)){
				break;
			}else{
				$save_file = FALSE;
			}
		}
		if($save_file){
			return file_put_contents($save_file,$data);
		}else{
			throw new Exception("保存的的配置文件不存在");
		}
	}

}

//end core/config.php
