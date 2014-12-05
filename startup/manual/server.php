<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>GreenPHP用户手册</title>
<style>
body{font-size:12px;}
h3{font-size:20px;margin-top:30px;}
h4{font-size:14px;}
ul{line-height:25px;}
b{background:#DDD;font-weight:normal;padding:0 4px;}
code{display:block;background:#FCF0C1;padding:0 10px;border:solid 1px #FADC96; line-height:20px;}
</style>
</head>
<body>
<h3>环境变量</h3>
<p>为了解决版本库中的文件更新到生产环境中时导致的配置出错，我们将一些配置移到了服务器的环境变量中。</p>
<code><pre>
LOCAL_API_URL  //API地址host成内网本机地址（http://localhost/api）
DB_HOST  //数据库主机         默认为 127.0.0.1
DB_PORT  //数据库端口         默认为 3306
DB_USER  //数据库用户名       默认为 root
DB_PASS  //数据库用户密码     默认为 空
MEM_HOST //Memcache主机       默认为 127.0.0.1
MEM_PORT //Memcache端口       默认为 11211
IS_DEV         //是否为开发环境0=正式环境1=开发环境  默认为 1
API_LANGUAGE   //对应语言en（默认中文）
</pre></code>
<p>在 core/boot.inc.php 文件中，将环境变量初始化为常量，并且在配置文件中使用，如果你不需要环境变量可以查看 <a href="<?php echo BASEURL ?>/startup/manual/config">配置文件</a> 修改。</p>
</body>
</html>
