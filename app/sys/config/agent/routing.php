<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DeskPRO/Resources/config/routing.php'));
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/routing.php'));
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/AgentBundle/Resources/config/routing.php'), '/agent');
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DevBundle/Resources/config/routing.php'), '/dev');

return $collection;
