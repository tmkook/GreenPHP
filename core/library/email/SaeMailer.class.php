<?php
/**
* SinaApp发送邮件工厂对象
*/
class sae_mailer extends email
{
    public function send($to,$title,$body,$additional=''){
        $to = implode(',',(array)$to);
        $opt = array(
            'to'=>$to,
            'subject'=>$title,
			'body'=>$body,
            'from'=>$this->from,
            'cc'=>$this->cc,
            'smtp_host'=>$this->host,
            'smtp_port'=>$this->port,
            'smtp_username'=>$this->user,
            'smtp_password'=>$this->pass,
            'content_type'=>$this->type,
        );
        $mail = new SaeMail($opt);
        if(!empty($additional)){
            $mail->setAttach((array)$additional);
        }
        $rst = $mail->send();
        if(!$rst && $this->debug){
            throw new Exception($mail->errmsg(),$mail->errno());
        }
        return $rst;
    }

}
