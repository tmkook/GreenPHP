<?php
return array(
	'room_minimum' => array(0,2000000,10000000), //进入场次所需最低余额
	'exchange_status'=>1, //是否开启兑换
	'alms' => array(1,20000,86400), //救济金(领取次数,领取金额,间隔秒)
	'goods' => array(
		//兑换的商品
		array(
			'name' => '50元手机充值卡',
			'type' => 'phone',
			'money'=> '12000',
			'value' => '50',
		),
		array(
			'name' => '100元手机充值卡',
			'type' => 'phone',
			'money'=> '24000',
			'value' => '100',
		),
		array(
			'name' => '50元QB卡',
			'type' => 'qb',
			'money'=> '12000',
			'value' => '50',
		),
		array(
			'name' => '100元QB卡',
			'type' => 'qb',
			'money'=> '24000',
			'value' => '100',
		),
		array (
			'name' => '1000万金币',
			'type' => 'gamemoney',
			'money' => '10000',
			'value' => '10000',
		),
	),
	'product' => array (
		array (
		  'name' => '12元礼包',
		  'description' => '12元得120W金币',
		  'productid' => 'come.moredoo.gold12',
		  'value' => 12,
		  'gamemoney' => 1200000,
		  'add' => 0,
		),
		array (
		  'name' => '18元礼包',
		  'description' => '18元得180W金币再赠送18W',
		  'productid' => 'come.moredoo.gold18',
		  'value' => 18,
		  'gamemoney' => 1800000,
		  'add' => 0.1,
		),
		array (
		  'name' => '30元礼包',
		  'description' => '30元得300W金币再赠送30W',
		  'productid' => 'come.moredoo.gold30n',
		  'value' => 30,
		  'gamemoney' => 3000000,
		  'add' => 0.1,
		),
		array (
		  'name' => '68元礼包',
		  'description' => '30元得680W金币再赠送102W',
		  'productid' => 'come.moredoo.gold68',
		  'value' => 68,
		  'gamemoney' => 6800000,
		  'add' => 0.15,
		),
		array (
		  'name' => '128元礼包',
		  'description' => '128元得1280W金币再赠送192W',
		  'productid' => 'come.moredoo.gold128',
		  'value' => 128,
		  'gamemoney' => 12800000,
		  'add' => 0.15,
		),
		array (
		  'name' => '198元礼包',
		  'description' => '198元得1980W金币再赠送297W',
		  'productid' => 'come.moredoo.gold198',
		  'value' => 198,
		  'gamemoney' => 19800000,
		  'add' => 0.15,
		),
		array (
		  'name' => '328元礼包',
		  'description' => '328元得3280W金币再赠送656W',
		  'productid' => 'come.moredoo.gold328',
		  'value' => 328,
		  'gamemoney' => 32800000,
		  'add' => 0.2,
		),
		array (
		  'name' => '648元礼包',
		  'description' => '648元得6480W金币再赠送1296W',
		  'productid' => 'come.moredoo.gold648',
		  'value' => 648,
		  'gamemoney' => 64800000,
		  'add' => 0.2,
		),
	),
);