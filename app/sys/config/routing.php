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
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/EmailBundle/Resources/config/email-routing.php');
$col->addPrefix('/email');
$collection->addCollection($col);

if (defined('DPC_IS_CLOUD')) {
    $col = $loader->import(DP_ROOT.'/src/Cloud/ApiBundle/Resources/config/api-routing.php');
    $col->addPrefix('/api');
    $collection->addCollection($col);
}

return $collection;
