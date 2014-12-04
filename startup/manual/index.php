<?php
$assign = array();
$assign['files'] = glob('../core/library/*.php');


$tpl = new Tpl(Config::get ('tpl.conf/startup'));
$tpl->display ('index.html', $assign);