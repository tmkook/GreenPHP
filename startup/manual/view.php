<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>运行结果</title>
<style type="text/css">
.code{background:#ffffdd;border:solid 1px #e3e197;padding:10px;}
</style>
</head>

<body>
<h2>源代码</h2>
<div class="code">
<pre>
<?php echo str_replace('<','&lt;',file_get_contents('./test/'.$_GET['file'].'.php')); ?>
</pre>
</div>

<h2>运行结果</h2>
<iframe src="test.php?file=<?php echo $_GET['file']; ?>" frameborder="0" width="100%" height="auto"></iframe>
</body>
</html>
