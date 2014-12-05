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
<h3>配置文件</h3>
<p>系统所有配置文件均统一放置在 source/config 目录下</p>
<p>数据库配置使用环境变量，如果你不需要配置环境变量，则打开 source/config/db.conf.php 修改对应的值</p>
<h3>获取配置</h3>
<code><pre>
//在项目中获取一项配置
Config::get('配置文件名/数组索引名');

//从多维数组中获取一项配置
Config::get('配置文件名/数组索引/数组索引');

//修改一项配置
Config::set('配置文件名/数组索引/数组索引','value');
Config::save(); //保存修改
</pre></code>
<p>* “配置文件名” 不包含 “.php” 后缀</p>
</body>
</html>
