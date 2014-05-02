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

$output_realtime = function($type, $buffer) {
	if ($type === 'err') {
		echo 'ERR: '.$buffer;
	} else {
		echo $buffer;
	}
};

$build_js = array(
	array('out' => 'Admin/build/js/build.js',        'name' => 'AdminLoad'),
	array('out' => 'AdminUpgrade/build/js/build.js', 'name' => 'AdminUpgradeLoad'),
	array('out' => 'AdminStart/build/js/build.js',   'name' => 'AdminStartLoad'),
	array('out' => 'Reports/build/js/build.js',      'name' => 'ReportsLoad'),
);

foreach ($build_js as $info) {

	$cmd = "r.js -o rjs-config.js out={$info['out']} name={$info['name']}";
	echo "-> $cmd\n";

	$proc = new \Symfony\Component\Process\Process($cmd, DP_WEB_ROOT.'/app');
	$proc->setTimeout(600);
	$proc->run($output_realtime);

	if (!$proc->isSuccessful()) {
		echo ("\nDetected error. Quitting.\n");
		exit($proc->getExitCode());
	}
}

echo "Done\n";