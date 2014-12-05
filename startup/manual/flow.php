<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Flow</title>
<style>
body{font-size:12px;}
ul{line-height:25px;}
b{background:#DDD;font-weight:normal;padding:0 4px;}
</style>
</head>
<body>
<h3>开始</h3>
<p>在使用GreenPHP前，如果您需要使用GreenPHP自带的后台与通用API时，请先将 source/sqls/tables.sql 导入到Mysql，并配置好数据库连接。</p>
<h3>目录说明</h3>
<pre style="line-height:25px;">
    -apps 项目文件
        -api    RestApi接口
        -admin  后台管理项目
        -web    前端项目
    -core 公共文件与配置
        -extends 项目扩展类
        -library 框架类库
    -source 框架资源(Linux中需配置递归读写权限)
        -config 项目配置文件
        -static 前端静态脚本文件
        -temp   模板编译目录
    -startup 框架文档工具
</pre> 
<h3>Web流程</h3>
<img src="<?php echo CDN ?>/static/manual/img/flow.png">
<h3>Api流程</h3>
<img src="<?php echo CDN ?>/static/manual/img/flow2.png">
</body>
</html>
