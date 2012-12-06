<?php

// Specify the ROOT
define('ROOT', realpath(dirname(__FILE__) . '/../'));

// Specifc the environmant
$_GLOBAL['ENV'] = getenv('APPLICATION_ENV');

// Include Newton
require(ROOT . '/code/vendor/Newton/Kernel.php');

// Initiolisze app
Newton\Kernel::initWeb();