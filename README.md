GreenPHP
========

##目录说明：
	*apps  项目文件
	*	web  前端项目
	*	admin 后台管理项目
	*core 公共文件与配置
	*	config 项目配置文件
	*	extends 项目扩展类
	*	library 框架类库
	*	boot.inc.php 框架初始化
	*static 前端静态脚本文件
	*temp 模板编译目录，Linux中需配置读写权限
	
##如何访问：
	*前端页面：http://host/dir/view/module/phpfilename
	*后台页面：http://host/dir/admin/module/phpfilename
	*host 主机域名
	*dir 定向到框架根目录（即 index.php 所在目录）
	*view 或 admin 固定（区分前台与后台）
	*module/web 或 module/admin 目录下的哪个模块
	*phpfilename 即 module/web 或 module/admin 的模块下对应的php文件名
		
