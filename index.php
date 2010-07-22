<?php
require_once './dppath.php';
require_once DP_ROOT.'/deskpro/DeskproKernel.php';

$kernel = new DeskproKernel('prod', false);
$kernel->handle()->send();
