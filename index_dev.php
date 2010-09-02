<?php
require('./dppath.php');
require(DP_ROOT.'/sys/Kernel.php');

use \DeskPRO\Kernel\Kernel;

$kernel = new Kernel('dev', false);
$kernel->handle()->send();
