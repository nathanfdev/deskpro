<?php if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

// for email templates and the like in the DpKernel, we need a temporary place for the new routes. They are REPLICATED
// here, from the new portal's .yml files.

//TODO #portal remove these routes

$collection->create('portal_index', array(
    'path' => '/',
));
$collection->create('portal_news_view', array(
    'path' => '/news/posts/{slug}',
));
$collection->create('portal_news_unsubscribe_all', array(
    'path' => '/news/posts/subscriptions/unsubscribe',
));
$collection->create('portal_downloads_view', array(
    'path' => '/downloads/files/{slug}',
));
$collection->create('portal_downloads_unsubscribe_all', array(
    'path' => '/downloads/files/subscriptions/unsubscribe',
));
$collection->create('portal_kb_view', array(
    'path' => '/kb/articles/{slug}',
));
$collection->create('portal_kb_unsubscribe_all', array(
    'path' => '/kb/articles/subscriptions/unsubscribe',
));
$collection->create('portal_agent_login', array(
    'path' => ' /impersonate/agent-login/{code}',
));

$collection->create('user', array(
    'path'        => '/',
    'controller'  => 'UserBundle:Portal:portal',
    'defaults'    => array('_locale' => 'en'),
));

return $collection;
