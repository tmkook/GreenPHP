<?php
include '../libraries/loader.php';
loader::autoload();
include_once "./test/{$_GET['file']}.php";
