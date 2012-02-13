<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/SysBundle/Resources/config/routing.php'), '/dp');

return $collection;
