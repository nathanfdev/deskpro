<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DeskPRO/Resources/config/routing.php'));
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/routing.php'));

return $collection;
