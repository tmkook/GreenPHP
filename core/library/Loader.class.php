<?php
/**
*--------------------------------------------------------------
* 文件加载管理
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
#--------------------------------------------------------------
Loader::autoload();    //自动加载
Loader::addLoad('./'); //添加加载目录
var_dump(Loader::loadClass('Config'));
---------------------------------------------------------------#
*/

class Loader
{
	protected static $load_path = array();

   /**
    * 添加加载路径
    *
    * @parame string|array $paths 加载路径
    * @return 如果目录不存在则触发一个Warning错误
    */
	public static function addPath($paths){
		foreach((array)$paths as $path){
			if(is_dir($path)){
				self::$load_path[] = realpath($path);
			}else{
				throw new Exception("加载目录 '{$path}' 不存在");
			}
		}
	}

   /**
    * 在加载路径中搜索文件并加载一次
    *
    * @parame string $file 加载文件
    * @return boolen
    */
	public static function loadOnce($file){
		foreach(self::$load_path as $path){
			$path = $path.'/'.$file.'.php';
			if(file_exists($path)){
				return include_once $path;
			}
		}
		return false;
	}

   /**
    * 在加载路径中搜索文件并加载
    *
    * @parame string $file 加载文件
    * @return boolen
    */
	public static function load($file){
		foreach(self::$load_path as $path){
			$path = $path.'/'.$file.'.php';
			if(file_exists($path)){
				return include $path;
			}
		}
		return false;
	}

   /**
    * 在系统类库中搜索文件并加载一次
    *
    * @parame string $file 加载文件
    * @return boolen
    */
	public static function loadClass($file){
		foreach(self::$load_path as $path){
			$path = $path.'/'.$file.'.class.php';
			if(file_exists($path)){
				return include_once $path;
			}
		}
		return false;
	}

   /**
    * 在加载路径中搜索文件如果不存在则在系统类库中搜索文件并加载一次
    *
    * @parame string $file 加载文件
    * @return 如果不存在直接抛出异常
    */
	public static function import($file){
		if( ! self::loadOnce($file) && ! self::loadClass($file)){
			throw new Exception("加载的文件 '{$file}' 不存在");
		}
	}

   /**
    * 注册自动加载路径
    *
    * @return 无返回值
    */
	public static function autoload(){
		spl_autoload_register(array(__CLASS__,'import'));
	}

}

//添加框架核心目录
Loader::addPath(dirname(__FILE__));
