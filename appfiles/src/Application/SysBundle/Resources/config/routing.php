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

$collection->add('sys_install', new Route(
	'/install',
	array('_controller' => 'SysBundle:Install:index'),
	array(),
	array()
));

$collection->add('sys_install_check', new Route(
	'/install/check',
	array('_controller' => 'SysBundle:Install:check'),
	array(),
	array()
));

$collection->add('sys_install_createtables', new Route(
	'/install/create-tables',
	array('_controller' => 'SysBundle:Install:createTables'),
	array(),
	array()
));

$collection->add('sys_install_createdata', new Route(
	'/install/create-data',
	array('_controller' => 'SysBundle:Install:createData'),
	array(),
	array()
));

$collection->add('sys_user_css', new Route(
	'/res/user/{filename}',
	array('_controller' => 'SysBundle:Resource:userCss'),
	array('filename' => '[a-zA-Z0-9_\\-]+\\.css'),
	array()
));


return $collection;
