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
<h3>1、程序开发</h3>
<p>后台的开发与Web开发一致，唯一区别就是后台 apps/admin 下的目录，会自动添加为后台管理页面板中的一级菜单，文件会添加为二级菜单。</p>
<h3>2、菜单翻译</h3>
<p>打开 core/extends/admin_roles/menus_lang_zhCN.php 文件，解释如下：</p>
<code><pre>
&lt;?php
return array(
	'logs'  //一级菜单目录名  =>  array(
		'name' => '系统日志', //菜单中文名称
		'icon' => 'icon-list-alt', //菜单图标
		'childs' => array( //二级菜单
			'query_logs'  //php文件名  => array('name'=>'PHP日志'), //文件名对应的中文名称
		)
	),
);
</pre></code>
<p>一级菜单图标使用bootstrap2.0中图标的类名，参考地址：<a href="http://v2.bootcss.com/base-css.html#icons" target="_blank">http://v2.bootcss.com/base-css.html#icons</a></p>
</body>
</html>
