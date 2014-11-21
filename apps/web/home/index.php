<?php
$assign = array ();
$assign['name'] = "GreenPHP";

$tpl = new Tpl(Config::get ('tpl.conf/web'));
$tpl->display ('index.html', $assign);