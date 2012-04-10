<?php if (!defined('DP_ROOT')) exit('No access');

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

$collection->add('dev_cm', new Route(
	'/client-messages',
	array('_controller' => 'DevBundle:ClientMessages:index'),
	array(),
	array()
));

$collection->add('dev_lang_index', new Route(
	'/lang/index',
	array('_controller' => 'DevBundle:Language:index'),
	array(),
	array()
));

$collection->add('dev_lang_find_problems', new Route(
	'/lang/find/problems',
	array('_controller' => 'DevBundle:Language:findProblems'),
	array(),
	array()
));

$collection->add('dev_lang_reformat_langfiles', new Route(
	'/lang/reformat/langfiles',
	array('_controller' => 'DevBundle:Language:reformatLanguageFiles'),
	array(),
	array()
));

$collection->add('dev_lang_export_all_po', new Route(
    '/lang/export/all/po',
    array('_controller' => 'DevBundle:Language:exportAllToPO'),
    array(),
    array()
));

return $collection;