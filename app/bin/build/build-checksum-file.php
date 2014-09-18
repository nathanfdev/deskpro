#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT . '/config.php');

require DP_ROOT . '/vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

require DP_ROOT . '/sys/autoload.php';

$start = microtime(true);
echo sprintf("Starting :: %.f\n", $start);

$checker = new \Application\DeskPRO\Distribution\ChecksumChecker();

$checker->load(function($count, $file, $hash) {
	if ($count % 100 == 0) {
		echo "Processed $count files...\n";
	}
});

$checker->dumpToStardnardFile();
$count = $checker->count();

$end = microtime(true);
echo sprintf("\nDone :: $count files :: %.f seconds\n", $end-$start);
