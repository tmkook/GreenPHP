<?php
/**
*--------------------------------------------------------------
* 对图像进行裁切和缩放
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
#--------------------------------------------------------------
$tb = new Images(file_get_contents('test.jpg'));
$tb->zoom(100,0)->display(); //显示图像
$tb->zoom(100,0)->save('path/filename'); //保存图像，无后缀名
---------------------------------------------------------------#
*/
class Images
{
    private $im;
	private $im_w;
	private $im_h;
    private $im_type;
    private $is_animat;
	private $exif_type = array(6677=>'bmp',7173=>'gif',13780=>'png',255216=>'jpeg');

    /**
    * 构造函数 初始化图像信息
    * 
    * @param $im 读取的文件数据
    */
    public function __construct($im){
		if(file_exists($im)){
			$im = file_get_contents($im);
		}
		$type_code = $this->typecode($im);
		if(!isset($this->exif_type[$type_code])) throw new Exception("不支持的图像格式");
		$this->im_type = $this->exif_type[$type_code];
		$this->is_animat = preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$im); //是否动画
		$this->im = imagecreatefromstring($im); //创建图像
        $this->im_w = imagesx($this->im);
        $this->im_h = imagesy($this->im);
    }

    /**
	* 图像信息
	* @return string jpeg gif png bmp
	*/
	public function imageinfo(){
		return array('w'=>$this->im_w,'h'=>$this->im_h,'type'=>$this->im_type,'animat'=>$this->is_animat);
	}
	
    /**
	* 图像去色
	*/
	public function gray(){
		imagefilter($this->im,IMG_FILTER_GRAYSCALE);
	}
	
    /**
	* 调整对比度
	* @param $rate integer 对比度
	*/
	public function contrast($rate){
		imagefilter($this->im,IMG_FILTER_CONTRAST,$rate);
	}
	
    /**
	* 调整亮度
	* @param $rate integer 亮度
	*/
	public function brightness($rate){
		imagefilter($this->im,IMG_FILTER_BRIGHTNESS,$rate);
	}

    /**
	* 裁切图片
	* 1.设置了宽和高的尺寸时图片会缩放到设定的尺寸
	* 2.宽或高只设置一项则另一项按比例缩放
	* 3.裁切将从设置的坐标位置开始默认为左上角
	*
	* @param $w 图片宽
	* @param $h 图片高
	* @param $x x坐标
	* @param $y y坐标
	*/
    public function crop($w,$h,$x=0,$y=0){
		if($this->im_type == 'flash' || $this->is_animat) return $this;
        if($w <= 0 || $h <= 0){
            throw new Exception("尺寸不能小于或等于 '0'");
        }
        $im_w = $this->im_w;
        $im_h = $this->im_h;
        if($w > $im_w || $h > $im_h){
            throw new Exception("裁切尺寸不能大于原始图");
        }
        $dst_im = imagecreatetruecolor($w,$h);
        imagealphablending($dst_im,false);
        imagesavealpha($dst_im,true);
        $white = imagecolorallocatealpha($dst_im,255,255,255,127);
        imagefill($dst_im,0,0,$white);
        imagecopyresampled($dst_im,$this->im,0,0,$x,$y,$w,$h,$w,$h);
        $this->im = $dst_im;
        return $this;
    }
    
    /**
	* 缩放图片
	* 1.设置了宽和高的尺寸时图片会缩放到设定的尺寸
	* 2.宽或高只设置一项则另一项按比例缩放
	*
	* @param $w 图片宽
	* @param $h 图片高
	*/
    public function zoom($w=0,$h=0){
		if($this->im_type == 'flash' || $this->is_animat) return $this;
        $im_w = $this->im_w;
        $im_h = $this->im_h;
        $x = $y = 0;
        if(empty($w) && $h > 0){ //自动定宽
            if($im_h > $h){
                $new_w = $h / $im_h * $im_w;
                $new_h = $h;
            }else{
                $new_w = $im_w;
                $new_h = $im_h;
            }
            $canvas_w = $new_w;
            $canvas_h = $new_h;
        }elseif(empty($h) && $w > 0){ //自动定高
            if($im_w > $w){
                $new_w = $w;
                $new_h = $w / $im_w * $im_h;
            }else{
                $new_w = $im_w;
                $new_h = $im_h;
            }
            $canvas_w = $new_w;
            $canvas_h = $new_h;
        }elseif($w > 0 && $h > 0){ //固定宽高
            if($im_w > $im_h || $w < $h){
                $new_h = intval(($w / $im_w) * $im_h);
                $new_w = $w;
            }else{
                $new_h = $h;
                $new_w = intval(($h / $im_h) * $im_w);
            }
            $x = intval(($w - $new_w) / 2); //画布x间距
            $y = intval(($h - $new_h) / 2); //画布y间距
            $canvas_w = $w;
            $canvas_h = $h;
        }else{ //无缩放
            $canvas_w = $new_w = $im_w;
            $canvas_h = $new_h = $im_h;
        }
        $dst_im = imagecreatetruecolor($canvas_w,$canvas_h);
        imagealphablending($dst_im,false);
        imagesavealpha($dst_im,true);
        $white = imagecolorallocatealpha($dst_im,255,255,255,127);
        imagefill($dst_im,0,0,$white);
        imagecopyresampled($dst_im,$this->im,$x,$y,0,0,$new_w,$new_h,$im_w,$im_h);
        $this->im = $dst_im;
        return $this;
    }
    
    /**
	* 显示图片
	*/
    public function display(){
        header("Content-type:image/{$this->im_type}");
		$func = "image{$this->im_type}";
        $func($this->im);
    }
    
    /**
	* 保存图片
	*
	* @param $path 保存路径
	* @return boolen
	*/
    public function save($path){
		if($this->im_type == 'flash' || $this->is_animat){
			return file_put_content($path,$this->im);
		}
		$func = "image{$this->im_type}";
        return $func($this->im,$path);
    }
	
    /**
	* 取得图像对象
	*
	* @return $this->im
	*/
	public function get(){
		return $this->im;
	}

    //获取图像类型
	private function typecode($im){
		$bin = substr($im,0,2);
		$str_info  = @unpack("C2chars", $bin);
		return intval($str_info['chars1'].$str_info['chars2']);
	}
}
