<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
$col->addPrefix('/api/v2');
$collection->addCollection($col);

// NEW PORTAL ROUTES
$col = $loader->import(DP_ROOT.'/src/DeskPRO/Bundle/PortalBundle/Resources/config/routing_portal.yml');
$collection->addCollection($col);

//
// to be removed shortly (old routes)
//
$col = $loader->import(DP_ROOT.'/src/Application/UserBundle/Resources/config/user-routing.php');
$collection->addCollection($col);
//
//
//


$col = $loader->import(DP_ROOT.'/src/Application/LegacyApiBundle/Resources/config/api-routing.php');
$col->addPrefix('/api');
$collection->addCollection($col);

if (defined('DPC_IS_CLOUD')) {
    $col = $loader->import(DP_ROOT.'/src/Cloud/LegacyApiBundle/Resources/config/api-routing.php');
    $col->addPrefix('/api');
    $collection->addCollection($col);
}

return $collection;
