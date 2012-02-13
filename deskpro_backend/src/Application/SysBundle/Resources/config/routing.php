<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('sys', new Route(
	'/',
	array('_controller' => 'SysBundle:Main:index'),
	array(),
	array()
));

$collection->add('sys_test', new Route(
	'/test',
	array('_controller' => 'SysBundle:Test:test'),
	array(),
	array()
));

$collection->add('sys_user_css', new Route(
	'/res/user/{filename}',
	array('_controller' => 'SysBundle:Resource:userCss'),
	array('filename' => '[a-zA-Z0-9_\\-]+\\.css'),
	array()
));

$collection->add('sys_log_js_error', new Route(
	'/log-js-error.json',
	array('_controller' => 'SysBundle:Misc:logJsError'),
	array(),
	array()
));


return $collection;
