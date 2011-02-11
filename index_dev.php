<?php
require('./dppath.php');
require(DP_ROOT.'/sys/Kernel.php');

use \DeskPRO\Kernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel('dev', true);
$kernel->handle(Request::createFromGlobals())->send();
