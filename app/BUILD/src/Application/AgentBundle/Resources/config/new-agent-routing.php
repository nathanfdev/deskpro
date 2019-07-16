<?php

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
