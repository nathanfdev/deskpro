<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DeskPRO/Resources/config/dp-routing.php'));
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/user-routing.php'));

$col = $loader->import(DP_ROOT.'/src/Application/AgentBundle/Resources/config/agent-routing.php');
$col->addPrefix('/agent');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/AdminInterfaceBundle/Resources/config/admin-interface-routing.php');
$col->addPrefix('/admin');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/ApiBundle/Resources/config/api-routing.php');
$col->addPrefix('/api');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/ReportsInterfaceBundle/Resources/config/reports-interface-routing.php');
$col->addPrefix('/reports');
$collection->addCollection($col);

return $collection;
