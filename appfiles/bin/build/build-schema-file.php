#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT.'/sys/system.php';

$kernel = new \DeskPRO\Kernel\CliKernel('dev', true);

$_SERVER['argv'] = array('x', 'dpdev:generate-schema-file', '-w');

$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->run();
