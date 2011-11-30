<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('dev', new Route(
	'/',
	array('_controller' => 'DevBundle:Main:index'),
	array(),
	array()
));

$collection->add('dev_phpinfo', new Route(
	'/php-info',
	array('_controller' => 'DevBundle:Main:phpInfo'),
	array(),
	array()
));

$collection->add('dev_testemail_usernewticket', new Route(
	'/test-email/user-new-ticket',
	array('_controller' => 'DevBundle:TestEmail:userNewTicket'),
	array(),
	array()
));

$collection->add('dev_seefile', new Route(
	'/see-file',
	array('_controller' => 'DevBundle:Main:seeFile'),
	array(),
	array()
));

$collection->add('dev_run_workerjob', new Route(
	'/run-worker-job',
	array('_controller' => 'DevBundle:Main:runWorkerJob'),
	array(),
	array()
));

$collection->add('dev_models', new Route(
	'/models',
	array('_controller' => 'DevBundle:Models:index'),
	array(),
	array()
));

$collection->add('dev_models_getsql', new Route(
	'/models/get-sql',
	array('_controller' => 'DevBundle:Models:getSql'),
	array(),
	array()
));

$collection->add('dev_models_regenerateproxies', new Route(
	'/models/regenerate-proxies',
	array('_controller' => 'DevBundle:Models:regenerateProxies'),
	array(),
	array()
));

$collection->add('dev_test', new Route(
	'/test',
	array('_controller' => 'DevBundle:Test:index'),
	array(),
	array()
));

$collection->add('dev_phptest', new Route(
	'/php-test',
	array('_controller' => 'DevBundle:Main:phpTest'),
	array(),
	array()
));

$collection->add('dev_phptest_run', new Route(
	'/php-test/run',
	array('_controller' => 'DevBundle:Main:phpTestRun'),
	array(),
	array()
));

$collection->add('dev_datagen', new Route(
	'/data-generator',
	array('_controller' => 'DevBundle:DataGenerator:index'),
	array(),
	array()
));

$collection->add('dev_datagen_run', new Route(
	'/data-generator/run',
	array('_controller' => 'DevBundle:DataGenerator:run'),
	array(),
	array()
));

$collection->add('dev_install', new Route(
	'/install',
	array('_controller' => 'DevBundle:Install:index'),
	array(),
	array()
));

$collection->add('dev_install_check', new Route(
	'/install/check',
	array('_controller' => 'DevBundle:Install:check'),
	array(),
	array()
));

$collection->add('dev_install_createtables', new Route(
	'/install/create-tables',
	array('_controller' => 'DevBundle:Install:createTables'),
	array(),
	array()
));

$collection->add('dev_install_createdata', new Route(
	'/install/create-data',
	array('_controller' => 'DevBundle:Install:createData'),
	array(),
	array()
));

$collection->add('dev_build', new Route(
	'/build',
	array('_controller' => 'DevBundle:Build:index'),
	array(),
	array()
));

$collection->add('dev_build_genclass', new Route(
	'/build/gen-build-class',
	array('_controller' => 'DevBundle:Build:genBuildClass'),
	array(),
	array()
));

$collection->add('dev_build_upgrade', new Route(
	'/build/upgrade',
	array('_controller' => 'DevBundle:Build:upgrade'),
	array(),
	array()
));

$collection->add('dev_build_upgrade_do', new Route(
	'/build/upgrade-do',
	array('_controller' => 'DevBundle:Build:upgradeDo'),
	array(),
	array()
));

$collection->add('dev_cm', new Route(
	'/client-messages',
	array('_controller' => 'DevBundle:ClientMessages:index'),
	array(),
	array()
));


return $collection;
