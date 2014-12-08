<?php
$files = glob('../core/library/*.php');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>GreenPHP用户手册</title>
<style type="text/css">
@charset "utf-8";
/*reset*/
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td{margin:0;padding:0;} 
html,body{font:12px arial;color:#333;height:100%;background:#FFF;width:100%;height:100%;overflow:hidden}
table{border-collapse:collapse;border-spacing:0;}
fieldset,img{border:0;} 
ol,ul{list-style:none;} 
h1,h2,h3,h4,h5,h6{font-size:100%;}
input, textarea, select{*font-size:100%;}
a{color:#0370DA;text-decoration:none;}
a:hover{text-decoration:underline;}

/*Global*/
.left{float:left;}
.right{float:right;}
.clean-self:before, .clean-self:after{content:"";display:table;}
.clean-self:after{clear:both;}
.clean-self{zoom:1;}
.clean{clear:both;display:block;}

.header{background:#56A6E6;}
.header .logo a{color:#FFF;display:inline-block;font-weight:bold;font-size:22px;width:120px;height:50px;line-height:50px;text-align:center;text-decoration:none;margin:0 50px;}
.header .header-nav li{float:left;}
.header .header-nav li a{color:#FFF;display:inline-block;width:80px;height:50px;line-height:50px;text-align:center;text-decoration:none;}
.header .header-nav li a:hover{background:#0096FF;}

.menu{width:16%;border-right:solid 1px #CCC;height:92%;}
.menu h2{padding:5px 10px;font-size:14px;margin-top:10px;}
.menu ul li a{display:block;padding:5px 20px;text-decoration:none;}
.menu li a:hover{background:#eff7ff;}

.body{width:83%;height:92%;border:none;border-left:solid 1px #CCC;}
</style>
</head>
<body>
<div class="header clean-self">
	<div class="logo left"><a href="#">GreenPHP<!--<img src="../libraries/base/logo.jpg" />--></a></div>
    <ul class="header-nav left">
    	<li><a href="<?php echo BASEURL ?>/startup/">首页</a></li>
		<li><a href="http://<?php echo $_SERVER['HTTP_HOST'].BASEURL ?>/startup/docs/api" target="_blank">API文档</a></li>
    	<li><a href="https://github.com/tmkook/GreenPHP/issues" target="_blank">讨论交流</a></li>
        <li><a href="https://github.com/tmkook/GreenPHP" target="_blank">贡献代码</a></li>
    </ul>
</div>
<div class="menu left">
    <h2>开始</h2>
    <ul>
    	<li><a href="<?php echo BASEURL ?>/startup/manual/flow"   target="_main">框架结构</a></li>
        <li><a href="<?php echo BASEURL ?>/startup/manual/webdev" target="_main">web开发</a></li>
		<li><a href="<?php echo BASEURL ?>/startup/manual/apidev" target="_main">api开发</a></li>
		<li><a href="<?php echo BASEURL ?>/startup/manual/admdev" target="_main">后台开发</a></li>
    </ul>
	<h2>布署</h2>
    <ul>
    	<li><a href="<?php echo BASEURL ?>/startup/manual/server" target="_main">环境变量</a></li>
		<li><a href="<?php echo BASEURL ?>/startup/manual/config" target="_main">配置文件</a></li>
		<li><a href="<?php echo BASEURL ?>/startup/manual/chmod" target="_main">文件权限</a></li>
    </ul>
	<!--
	<h2>类库</h2>
    <ul>
    	<?php foreach($files as $file): $file = explode('/',$file); $file = end($file) ?>
        <li><a href="<?php echo BASEURL ?>/startup/manual/test?file=<?php echo $file ?>" target="_main"><?php echo $file ?></a></li>
        <?php endforeach; ?>
    </ul>
	-->
</div>

<iframe src="<?php echo BASEURL ?>/startup/manual/about" class="body left" name="_main"></iframe>
</body>
</html>
