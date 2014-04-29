<?php
class mail_mailer extends email
{

	function send($to,$title,$body,$additional=''){
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset={$this->charset}\r\n";
		$headers .= "From: {$this->from} <{$this->from}>\r\n";
		$title    = "=?{$this->charset}?B?".base64_encode($title)."?=";
		return mail($to, $title, $body, $headers);
	}	

}