<?php
return array(
	//用户名注册
	'username_register'=> array(
		array(
			'field'=>'username',
			'label'=>'用户名',
			'rules'=> array('required','minlen'=>5,'maxlen'=>18)
		),
    ),
	//设备号注册
	'regapp_register'=> array(
		array(
			'field'=>'regapp',
			'label'=>'设备号',
			'rules'=> array('required','safe')
		),
    ),
	
	//注册公共字段验证
    'register'=> array(
		array(
			'field'=>'password',
			'label'=>'密码',
			'rules'=> array('required','minlen'=>6,'maxlen'=>32)
		),
		array(
			'field'=>'platform',
			'label'=>'平台',
			'rules'=> array('safe')
		),
		array(
			'field'=>'regapp',
			'label'=>'平台',
			'rules'=> array('safe')
		),
		array(
			'field'=>'username',
			'label'=>'用户名',
			'rules'=> array('minlen'=>5,'maxlen'=>18)
		),
		array(
			'field'=>'mobile',
			'label'=>'手机',
			'rules'=> array('phone')
		),
		array(
			'field'=>'email',
			'label'=>'邮箱',
			'rules'=> array('email')
		),
		array(
			'field'=>'nickanme',
			'label'=>'昵称',
			'rules'=> array('safe','maxlen'=>16)
		),
		array(
			'field'=>'realname',
			'label'=>'真实姓名',
			'rules'=> array('str')
		),
		array(
			'field'=>'regapp',
			'label'=>'平台',
			'rules'=> array('safe')
		),
    ),
	//用户名登录
    'id_login'=> array(
        array(
            'field'=>'uin',
            'label'=>'用户id',
            'rules'=> array('required','integer')
        ),
    ),
	
	/*//用户名登录
    'username_login'=> array(
        array(
            'field'=>'username',
            'label'=>'帐号',
            'rules'=> array('required','minlen'=>5,'maxlen'=>18)
        ),
    ),
	
	//设备登录
	'regapp_login'=> array(
        array(
            'field'=>'regapp',
            'label'=>'注册设备',
            'rules'=> array('required','safe')
        ),
    ),*/
	
	//登录公共字段验证
	'login'=> array(
        array(
            'field'=>'username',
            'label'=>'帐号',
            'rules'=> array('minlen'=>5,'maxlen'=>18)
        ),
		array(
			'field'=>'regapp',
			'label'=>'平台',
			'rules'=> array('safe')
		),
        array(
            'field'=>'password',
            'label'=>'密码',
            'rules'=> array('required','minlen'=>6,'maxlen'=>32)
        ),
		array(
            'field'=>'logapp',
            'label'=>'登录设备',
            'rules'=> array('required')
        ),
    ),

);