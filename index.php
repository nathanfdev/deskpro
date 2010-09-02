<?php
require('./dppath.php');
require(DP_ROOT.'/sys/Kernel.php');

use \DeskPRO\Kernel\Kernel;

$kernel = new Kernel('prod', false);
$kernel->handle()->send();
