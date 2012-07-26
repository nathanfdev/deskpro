<?php
define('DP_ROOT', realpath(dirname(__FILE__) . '/../'));
require_once DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';

$fatal = array();

foreach (deskpro_install_check_reqs() as $type => $level) {
	if ($level == 'fatal') {
		$fatal[] = $type;
	}
}

if ($fatal) {
	echo "Errors detected: " . implode(',', $fatal);
} else {
	echo "OKAY";
}

echo "\n";