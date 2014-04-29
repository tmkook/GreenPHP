<?php
/**
*--------------------------------------------------------------
* 对字符串或数组加密成字符串
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright 修改自discuz authcode
* @version $Id$
*--------------------------------------------------------------
$authcode = new Encrypt('mykey',1800);
$encode = $authcode->encode('myinvitecode'); //加密后
$decode = $authcode->decode($encode); //解密后
echo "加密：{$encode}<br>";
echo "解密：{$decode}<br>";
*--------------------------------------------------------------
*/
class Encrypt
{
	private $key; //密钥
	private $expire; //有效时间
	private $safelen; //安全长度，越长越安全
	protected $expiretime; //过期时间
	
   /**
    * 构造函数
    *
    * @parame string $key 密钥
	* @expire integer $expire 有效时间
	* @parame integer $safelen 安全长度，越长越安全
    */
	public function __construct($key,$expire=0,$safelen=4){
		if(empty($key)) throw new Exception("密钥不能为空");
		$this->key = md5($key);
		$this->expire = intval($expire);
		$this->safelen = intval($safelen);
	}

   /**
    * 加密
    *
    * @parame string|array $string 需要加密的数据
	* @return string 加密后的字符串
    */
	public function encode($string) {
		if(is_array($string)) $string = json_encode($string);
		$ckey_length = $this->safelen;
		$keya = md5(substr($this->key, 0, 16));
		$keyb = md5(substr($this->key, 16, 16));
		$keyc = substr(md5(microtime()), -$ckey_length);

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$this->expiretime = $this->expire ? $this->expire + time() : 0;
		$string = sprintf('%010d', $this->expiretime).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		return rawurlencode($keyc.base64_encode($result));
	}

   /**
    * 解密
    *
    * @parame string $string 需要解密的字符串
	* @return string|array 解密后的数据
    */
	public function decode($string) {
		$string = rawurldecode($string);
		$ckey_length = $this->safelen;
		$keya = md5(substr($this->key, 0, 16));
		$keyb = md5(substr($this->key, 16, 16));
		$keyc = substr($string, 0, $ckey_length);

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = base64_decode(substr($string, $ckey_length));
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		$this->expiretime = substr($result, 0, 10);
		if(($this->expiretime == 0 || $this->expiretime > time()) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			$result = substr($result, 26);
			$arr = json_decode($result,true);
			if(!empty($arr)){
				return $arr;
			}else{
				return $result;
			}
		} else {
			return '';
		}
	}
	
   /**
    * 取得密文到期时间戳
    *
    * @parame string $string 需要解密的字符串
	* @return string|array 解密后的数据
    */
	public function getExpire(){
		return $this->expiretime;
	}
	
}
