<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();
$collection->addCollection($loader->import(DP_ROOT.'/src/Application/DeskPRO/Resources/config/dp-routing.php'));

// NEW API ROUTES
$col = $loader->import(DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Resources/config/routing_api.yml');
$collection->addCollection($col);

// NEW PORTAL ROUTES
$col = $loader->import(DP_ROOT.'/src/DeskPRO/Bundle/PortalBundle/Resources/config/routing_portal.yml');
$collection->addCollection($col);

// to be removed shortly (old routes)

$col = $loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/user-routing.php');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/LegacyApiBundle/Resources/config/api-routing.php');
$col->addPrefix('/api');
$collection->addCollection($col);

if (defined('DPC_IS_CLOUD')) {
    $col = $loader->import(DP_ROOT.'/src/Cloud/LegacyApiBundle/Resources/config/api-routing.php');
    $col->addPrefix('/api');
    $collection->addCollection($col);

    // ClOUD BILLING ROUTES
    $col = $loader->import(DP_ROOT.'/src/DeskPROCloud/Bundle/CloudBillingBundle/Resources/config/routing.yml');
    $collection->addCollection($col);
}

return $collection;
