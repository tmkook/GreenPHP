<?php
//显示头像
//w = 头像宽
//h = 头像高
//u = 图像文件名（推荐使用用户id生成头像文件）
$img = new Uploader();
$img->getFace($_GET['u'],$_GET['w'],$_GET['h']);




/*
//如何上传头像
if(!empty($_FILES['face'])){
	$upload = new Uploader();
	$rst = $upload->uploadFace($_FILES['face']['tmp_name'],$userid);
	if($rst){
		exit('{"code":0}');
	}else{
		exit('{"code":1,"msg":"上传失败"}');
	}
}
*/
