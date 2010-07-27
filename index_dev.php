<?php

//phpinfo(); exit;

require_once './dppath.php';
require_once DP_ROOT.'/deskpro/DeskproKernel.php';

$kernel = new DeskproKernel('dev', true);
$kernel->handle()->send();