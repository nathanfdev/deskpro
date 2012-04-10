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

$collection->add('dev_lang_check_langfiles', new Route(
	'/lang/check/langfiles',
	array('_controller' => 'DevBundle:Language:checkLanguageFiles'),
	array(),
	array()
));

$collection->add('dev_lang_find_phrases_php', new Route(
	'/lang/find/phrases/php',
	array('_controller' => 'DevBundle:Language:findPhrasesInPHPFiles'),
	array(),
	array()
));

$collection->add('dev_lang_find_phrases_twig', new Route(
	'/lang/find/phrases/twig',
	array('_controller' => 'DevBundle:Language:findPhrasesInTwigFiles'),
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