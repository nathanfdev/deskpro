<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DeskPRO/Resources/config/dp-routing.php'));
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/user-routing.php'));

$col = $loader->import(DP_ROOT.'/src/Application/AdminInterfaceBundle/Resources/config/admin-interface-routing.php');
$col->addPrefix('/adm');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/AgentBundle/Resources/config/agent-routing.php');
$col->addPrefix('/agent');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/BillingBundle/Resources/config/billing-routing.php');
$col->addPrefix('/billing');
$collection->addCollection($col);

return $collection;
