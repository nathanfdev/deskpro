<?php
require_once './dppath.php';
require_once DP_ROOT.'/deskpro/DeskproKernel.php';

$kernel = new DeskproKernel('dev', false);
$kernel->handle()->send();
