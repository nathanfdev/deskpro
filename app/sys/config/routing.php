<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

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

$col = $loader->import(DP_ROOT.'/src/Application/InterfaceBundle/Resources/config/interface-routing.php');
$col->addPrefix('/viewer');
$collection->addCollection($col);

if (defined('DPC_IS_CLOUD')) {
    $col = $loader->import(DP_ROOT.'/src/Cloud/ApiBundle/Resources/config/api-routing.php');
    $col->addPrefix('/api');
    $collection->addCollection($col);
}

return $collection;
