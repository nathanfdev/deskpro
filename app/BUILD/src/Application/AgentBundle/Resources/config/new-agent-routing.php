<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

$collection->create(
    'new_agent',
    [
        'path'       => '/',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_tickets',
    [
        'path'       => '/tickets',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_new_tickets',
    [
        'path'       => '/new-tickets',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_tasks',
    [
        'path'       => '/tasks',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_example',
    [
        'path'       => '/example',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_crm',
    [
        'path'       => '/crm',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_chat',
    [
        'path'       => '/chat',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_feedback',
    [
        'path'       => '/feedback',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_publish',
    [
        'path'       => '/publish',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_login',
    [
        'path'       => '/login',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;
$collection->create(
    'react_agent_welcome',
    [
        'path'       => '/welcome',
        'controller' => 'AgentBundle:AgentChrome:agentChrome',
    ]
)
;

return $collection;
