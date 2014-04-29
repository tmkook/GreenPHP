<?php
/**
*--------------------------------------------------------------
* 邮件工厂
*--------------------------------------------------------------
* 最后修改时间 2012-1-8 Leon
* @author Leon(tmkook@gmail.com)
* @date 2011-2-27
* @copyright GreenPHP
* @version $Id$
#--------------------------------------------------------------
$conf = array(
	'default' => array(
		'driver'=>'SmtpMailer',
		'is_auth'=>true,
		'host'=>'smtp.163.com',
		'port'=>25,
		'type'=>'HTML',
		'debug' => false,
		'charset' => 'UTF-8'
		'from'=>'dllx2007@163.com',
		'user'=>'dllx2007@163.com',
		'pass'=>'leonde',
	)
);
$smtp = Email::connect($conf);
var_dump($smtp->send('7040494@qq.com','标题测试','test')); //发送邮件
---------------------------------------------------------------#
*/
abstract class Email
{
    
    protected $is_auth = false;
    protected $host    = 'localhost';
    protected $port    = 25;
    protected $type    = 'HTML';
    protected $debug   = false;
	protected $charset = 'UTF-8';
	protected $from    = 'service@greenphp.com';
    protected $user;
    protected $pass;
    protected $cc;
    protected $bcc;

    public function connect($conf){
		$driver = $conf['driver'];
        require_once dirname(__FILE__).'/email/'.$driver.'.class.php';
        $obj = new $driver();
		if( ! $obj instanceof self) throw new Exception("邮件类 '{$driver}' 未继承 'Email'");
		$obj->initialize($conf);
		return $obj;
    }

    public final function initialize($conf){
        foreach($conf as $key=>$val){
			$this->$key = $val;
		}
    }
    
    public function cc($cc){
        $this->cc = implode(',',(array)$cc);
    }
    
    public function bcc($bcc){
        $this->bcc = implode(',',(array)$bcc);
    }
    
    abstract public function send($to,$title,$body,$additional=array());

}
