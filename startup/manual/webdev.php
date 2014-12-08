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
<h3>1、hello world</h3>
<p>创建页面文件 apps/web/helloworld/index.php 并输入以下代码</p>
<code>
<pre>
&lt;?php
echo "hello world";
</pre>
</code>
<p>访问 http://localhost<?php echo BASEURL ?>/view/helloworld/index 即可看到 <b>hello world</b></p>


<h3>2、使用模板</h3>
<p>创建页面文件 apps/web/helloworld/tpl.php 并输入以下代码</p>
<code>
<pre>
&lt;?php
$assign = array();
$assign['helloworld'] = "hello world";
$tpl = new Tpl(Config::get('tpl.conf/web')); //将模板配置传给 Tpl 类并实例化
$tpl->display("tpl.html",$assign); //加载模板文件，并将参数传给模板
</pre>
</code>
<p>创建模板文件 apps/web/helloworld/tpl/tpl.html 并输入以下代码</p>
<code>
<pre>
&lt;html&gt;
&lt;body&gt;
&lt;h1&gt;{$helloworld}&lt;h1&gt;
&lt;body&gt;
&lt;/html&gt;
</pre>
</code>
<p>访问 http://localhost<?php echo BASEURL ?>/view/helloworld/tpl 即可看到<b>hello world</b>的标题</p>


<h3>2.1模板语法</h3>
<p>输出变量使用 <b>{$name}</b> 会被解析为带 "echo" 的PHP标签。如<b>&lt;?php echo $name ?&gt;</b> “{}”中间只能是变量、常量、环境变量。</p>
<p>PHP代码使用 <b>&lt;!--{if(1) echo $name;}--&gt;</b> 其中“&lt;!--{}--&gt;”会被替换为PHP标签。如<b>&lt;?php if(1) echo $name; ?&gt;</b>。 “&lt;!--{}--&gt;”中间可以是任何可执行的PHP代码。</p>
<p>* 第一种只适合输出变量，第二种适合循环列表与模板中的逻辑判断。标签不能断行和加空格。</p>
<p>在模板中循环一个列表：</p>
<code>
<pre>
&lt;!--{foreach($list as $key=>$item):}--&gt;
	{$item} //输出列表变量
&lt;!--{endforeach;}--&gt;
</pre>
</code>


<h3>3、使用数据库</h3>
<p>我们继续按第2步的代码，从数据库中查出content等于“hello world”的记录来显示到模板中。</p>
<code>
<pre>
&lt;?php
$assign = array();
$db = Database::connect(Config::get('db.conf/default')); //连接数据库
$result = $db->from('tablename')->where("content='hello world'")->fetch(); //查询tablename表中content等于hello world的记录。
$assign['helloworld'] = $result; //将查出的记录赋予模板变量
$tpl = new Tpl(Config::get('tpl.conf/web')); //将模板配置传给 Tpl 类并实例化
$tpl->display("tpl.html",$assign); //加载模板文件，并将参数传给模板
</pre>
</code>
<p>更多数据库操作请参考下面</p>
<code>
<pre>
&lt;?php
$db = Database::connect(Config::get('db.conf/default'),true); //连接数据库
$insertid = $db->insertInto(’tablename‘, array('field'=>'value'))->execute(); //写入表记录
$bool = $db->delete('tablename')->where('field','value')->execute(); //删除表记录
$bool = $db->update('tablename')->set(array('field'=>'value'))->where('field',$value)->execute(); //更新表记录
$result = $db->from('tablename')->where('field',$value)->fetchAll(); //查询多行表记录
$row = $db->from('tablename')->where('field',$value)->fetch(); //查询一行表记录
$column = $db->from('tablename')->where('field',$value)->fetchColumn("content"); //查询一个记录
还可以直接使用PDO
$pdo = $db->getPdo();
$pdo->query($sql)->fetchAll();

Database继承自FluentPDO，更多功能请查阅
<a href="http://fluentpdo.com/documentation.html#todo" target="_blank">http://fluentpdo.com/documentation.html#todo</a>
</pre>
</code>

<h3>4、调用API</h3>
<p>请求接口：<a href="http://localhost<?php echo BASEURL ?>/startup/docs/api?vm=common&vc=user&vt=sig" target="_blank">http://localhost<?php echo BASEURL ?>/startup/docs/api?vm=common&vc=user&vt=sig</a></p>
<p>使用Http类请求</p>
<code>
<pre>
&lt;?php
$param['uin']       = 2;
$param['password']  = md5("123456");
$param['logapp']    = "test";
$param['platform']  = 'web';
$param['regapp']    = 'web';
$rst = Http::request(APIBASEURL.'/common/user/sig',
	array(
	'parameter' => json_encode($param),
	'extparam' => '{"reqapp":"test"}')
);
$data = json_decode($rst,true);
if($data ['code'] == 100){
	// 登录成功
}else{
	//登录失败
}
</pre>
</code>
<p>使用扩展HttpQuery类请求</p>
<code>
<pre>
&lt;?php
$param['uin']       = 2;
$param['password']  = md5("123456");
$param['logapp']    = "test";
$param['platform']  = 'web';
$param['regapp']    = 'web';
$rst = HttpQuery::api('/common/user/sig',$param,array('reqapp'=>'test'));
$data = json_decode($rst,true);
if($data ['code'] == 100){
	// 登录成功
}else{
	//登录失败
}
</pre>
</code>


<p>看完以上例子你理解了吗？如果还想更深入了解可以查看“apps/admin/”下的文件有具体项目的完整代码供参考。</p>
</body>
</html>
