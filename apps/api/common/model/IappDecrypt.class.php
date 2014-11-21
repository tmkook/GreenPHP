<?php
class Math {
	private static $primes = array(2,3,5,7,11,13,17,19,23,29,31,37,41,43,47,53,59,61,67,71,73,79,83,89,97);
	public static function bin2int($string){
		$result = '0';
		$n = strlen($string);
		do {
			$result = bcadd(bcmul($result,'256'), ord($string{--$n}));
		} while ($n > 0);
		return $result;
	}
	public static function int2bin($num){
		$result = '';
		do {
			$result .= chr(bcmod($num, '256'));
			$num = bcdiv($num, '256');
		} while ($num != '0');
		return $result;
	}
	public static function bitLen($num) {
		$tmp = self::int2bin($num);
		$len = strlen($tmp) * 8;
		$tmp = ord($tmp {strlen($tmp) - 1});
		if (!$tmp)$len -= 8;
		else while(!($tmp & 0x80)){
				$len --;
				$tmp <<= 1;
			}
		return $len;
	}
	public static function byte2int($byte){
		if(is_array($byte)) $byte = implode('', $byte);
		//$byte = ltrim($byte, '0');
		$len = strlen($byte);
		$sign = substr($byte, 0, 1) == '-' ? true : false;
		$arr = Array();
		
		$intlen = intval(($len + 3) / 4);
		for($i = 0, $k = $len - 1; $i < $intlen; $i++){
			$arr[$i] = ord($byte[$k--]) & 0xff;
			$n = min(3,  $k + 1);
			for ($j=8; $j <= 8 * $n; $j += 8)
				$arr[$i] |= ((ord($byte[$k--]) & 0xff) << $j);
			$arr[$i] = bcmul($arr[$i], bcpow('4294967296', $i));
		}
		$sum = '0';
		foreach($arr as $v) $sum = bcadd($sum, $v);
		if($sign == true && $sum != '0') $sum = '-' . $sum;
		return $sum;
	}
	public static function int2byte($num){
		$arr = array();
		$bit = '';
		while(bccomp($num, '0') > 0){
			$asc = bcmod($num, '256');
			$bit = chr($asc) . $bit;
			$num = bcdiv($num, '256');
		}
		return $bit;
	}
	public static function dec2hex($num){
		$char = '0123456789abcdef';
		while (bccomp($num, '0') > 0){
			$k = bcmod($num, '16');
			$hex = $char[intval($k)] . $hex;
			$num = bcdiv($num, '16');
		}
		return $hex;
	}
	public static function hex2dec($num){
		$char = '0123456789abcdef';
		$num = strtolower($num);
		$len = strlen($num);
		$sum = '0';
		for($i = $len - 1, $k = 0; $i >= 0; $i--, $k++){
			$index = strpos($char, $num[$i]);
			$sum = bcadd($sum, bcmul($index, bcpow('16', $k)));
		}
		return $sum;
	}
	public static function mr_test($num, $base) { //Miller-Rabin Test 
		if ($num == '1') return false;
		$tmp = bcsub($num, '1');
		
		$k = 0;
		while(bcmod($tmp, '2') == '0') {
			$k++;
			$tmp = bcdiv($tmp, '2');
		}
		$tmp = bcpowmod($base, $tmp, $num);
		if ($tmp == '1') return true;	
		
		while ($k--){
			if (bcadd($tmp, '1') == $num) return true;
			$tmp = bcpowmod($tmp, '2', $num);
		}
		return false;
	}
	public static function is_prime($num) {
		if(in_array($num, self::$primes)) return true;
		for ($i = 0; $i < 7; $i++)
			if(self::mr_test($num, self::$primes[$i]) == false) return false;
		return true;
	}
	public static function get_prime($bit_len) {
		$k = intval($bit_len / 8);
		$cnt = $bit_len % 8;
		do {
			$str = '';
			for ($i = 0; $i < $k; $i++) $str .= chr(mt_rand(0, 0xff));
			$n = mt_rand(0, 0xff) | 0x80;
			$n >>= 8 - $cnt;
			$str .= chr($n);
			$num = self::bin2int($str);
			
			if (bcmod($num, '2') == '0') $num = bcadd($num, '1');
		
			while (self::is_prime($num) == false) $num = bcadd($num, '2');
		} while (self::bitLen($num) != $bit_len);
		return $num;
	}
	
	public static function get_gcd($a, $b){ //Euclidean
		while($b != '0'){
			$k = $b;
			$b = bcmod($a, $b);
			$a = $k;
		}
		return $a;
	}

	public static function get_modinv($num, $m){ // 1/$num mod $m
		$x = '1';
		$y = '0';
		$k = $m;
		do {
			$tmp = bcmod($num, $k);
			$q = bcdiv($num, $k);
			$num = $k;
			$k = $tmp;
			
			$tmp = bcsub($x, bcmul($y, $q));
			$x = $y;
			$y = $tmp;
		} while ($k != '0');
		if (bccomp($x, '0') < 0) $x = bcadd($x, $m);
		return $x;
	}
}

class RSAUtil{
	public $keylen = 64;
	
	
	private $_key = array();
	
	/**
	 * 获取随机质数
	 * @return
	 */
	public function get_primes(){
		return Math::get_prime($this->keylen);
	}
	
	/**
	 * 通过P,Q计算N值
	 * @param 一个素数p
	 * @param 一个素数q
	 * @return 返回P*Q的值n
	 */
	public function get_n($p, $q){
		return bcmul($p, $q);
	}
	
	/**
	 * 通过P,Q计算ran值 modkey
	 * @param 一个素数p,不能为空
	 * @param 一个素数q,不能为空
	 * @return 返回(P-1)*(Q-1)的值ran
	 */
	public function get_ran($p, $q){
		return bcmul(bcsub($p, '1'), bcsub($q, '1'));
	}
	
	/**
	 * 获取公钥
	 * @param $ran
	 * @return ;
	 */
	public function get_public_key($ran){
		$e = '0';
		do{
			$tmp = Math::get_prime($this->keylen);
			if(Math::get_gcd($tmp, $ran) == '1') $e = $tmp;
		}while(Math::get_gcd($tmp, $ran) != '1');
		return $e;
	}
	
	/**
	 * 获取私钥
	 * @param $ran
	 * @param $public_key
	 * @return
	 */
	public function get_private_key($ran, $public_key) {
		return Math::get_modinv($public_key, $ran);
	}
	/**
	 * 加密方法
	 * @param $string 需要加密的明文字符
	 * @param $e 公钥
	 * @param $n
	 * @return String
	 */
	public function encrypt($string, $e, $n){
		$bitlen = $this->keylen * 2 - 1;
		$bitlen = intval($bitlen / 8);
		$len = strlen($string);
		$arr = array();
		for($i = 0; $i < $len; $i += $bitlen)
			$arr[] = substr($string, $i, $bitlen);
		$index = count($arr) - 1;
		$len = strlen($arr[$index]);
		if($len < $bitlen) $arr[$index] = $arr[$index] . str_repeat(' ', $bitlen - $len);
		$data = array();
		foreach ($arr as $v){
			$v = Math::byte2int($v);
			$v = bcpowmod($v, $e, $n);
			$data[] = Math::dec2hex($v);
		}
		
		return implode(' ', $data);
	}
	/**
	 * 解密方法
	 * @param $string 需要解密的密文字符
	 * @param $d
	 * @param $n
	 * @return String
	 */
	public function decrypt($string, $d, $n){
		//解决某些机器验签时好时坏的bug
		//BCMath 里面的函数 有的机器php.ini设置不起作用
		//要在RSAUtil的方法decrypt 加bcscale(0);这样一行代码才行
		//要不有的机器计算的时候会有小数点 就会失败
		bcscale(0);
		
		$bln = $this->keylen * 2 - 1;
		$bitlen = ceil($bln / 8);
		$arr = explode(' ', $string);
		$data = '';
		foreach($arr as $v){
			$v = Math::hex2dec($v);
			$v = bcpowmod($v, $d, $n);
			$data .= Math::int2byte($v);
		}
		return trim($data);
	}
}

class IappDecrypt{		 
	public function validsign($trans_data,$sign,$key){
		$rsa = new RSAUtil();

		//解析key 需要从商户自服务提供的key中解析出我们的真正的key. 商户自服务提供的key = mybase64(private_key+mod_key);
		$key1 =  base64_decode($key);
		$key2 = substr($key1,40,strlen($key1)-40);
		$key3 = base64_decode($key2);
		//php 5.3环境用下面这个
		//list($private_key, $mod_key) = explode("+", $key3);
		list($private_key, $mod_key) = split("\\+", $key3);
		//使用解析出来的key，解密包体中传过来的sign签名值
		$sign_md5 = $rsa->decrypt($sign, $private_key, $mod_key);
		$msg_md5 = md5($trans_data);
		//echo "key3 : {$key3} <br/>\n";
		//echo "private_key : {$private_key} <br/>\n";
		//echo "mod_key : {$mod_key} <br/>\n";
		//echo "sign_md5 : {$sign_md5} <br/>\n";
		//echo "msg_md5 : {$msg_md5} <br/>\n";
		
		return strcmp($msg_md5,$sign_md5);
	}
	
	public function gensign($trans_data,$key){
		$rsa = new RSAUtil();

		//解析key 需要从商户自服务提供的key中解析出我们的真正的key. 商户自服务提供的key = mybase64(private_key+mod_key);
		$key1 =  base64_decode($key);
		$key2 = substr($key1,40,strlen($key1)-40);
		$key3 = base64_decode($key2);
		//list($private_key, $mod_key) = split("\\+", $key3);
		 if(phpversion () > "5.3"){
            list ( $private_key, $mod_key ) = explode ( "+", $key3 );
        }else{
            list($private_key, $mod_key) = split("\\+", $key3);
        }

		//使用解析出来的key，解密包体中传过来的sign签名值
		$msg_md5 = md5($trans_data);
		return $rsa->encrypt($msg_md5, $private_key, $mod_key);
	}
}
