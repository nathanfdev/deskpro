#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../web/'));
define('DP_CONFIG_FILE', DP_WEB_ROOT . '/config.php');

require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT . '/bin/build/php-path.php';

foreach ($build_js as $info) {

	$cmd = "build-web.sh";
	echo "-> $cmd\n";

	$proc = new \Symfony\Component\Process\Process($cmd, DP_WEB_ROOT.'/app/bin');
	$proc->setTimeout(600);
	$proc->run($output_realtime);

	if (!$proc->isSuccessful()) {
		echo ("\nDetected error. Quitting.\n");
		exit($proc->getExitCode());
	}
}

echo "Done\n";