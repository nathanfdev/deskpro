<?php if (!defined('DP_ROOT')) exit('No access');

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Verify auth
#------------------------------

header("Content-Type: plain/text");

if (!defined('DP_TESTING_MODE_AUTH')) {
	echo "status(DP_TESTING_MODE_AUTH_UNDEFINED)";
	exit(1);
}

if (!isset($_GET['auth']) || $_GET['auth'] != DP_TESTING_MODE_AUTH) {
	echo "status(DP_TESTING_MODE_AUTH_INVALID)";
	exit(1);
}

#------------------------------
# Enable testing
#------------------------------

if (isset($_GET['disable'])) {

	unlink(DP_WEB_ROOT.'/running_tests.trigger');

	if (is_file(DP_WEB_ROOT.'/running_tests.trigger')) {
		echo "status(DP_TESTING_MODE_FAILED_TRIGGER)";
		exit(1);
	}

	echo "status(DP_TESTING_MODE_DISABLED)";
	exit(0);

} else {
	$php = @$DP_CONFIG['php_path'] ?: "php";
	$path = DP_ROOT;
	$setname = escapeshellarg(@$_GET['setname'] ?: "");

	$cmd = "$php $path/testing/bin/reset-product-db $setname";
	echo "> $cmd\n";

	$ret = null;
	passthru($cmd, $ret);

	echo "\n\n";

	if ($ret != 0) {
		echo "status(DP_TESTING_MODE_FAILED_DB)";
		exit(1);
	}

	file_put_contents(DP_WEB_ROOT.'/running_tests.trigger', time());
	if (!is_file(DP_WEB_ROOT.'/running_tests.trigger')) {
		echo "status(DP_TESTING_MODE_FAILED_TRIGGER)";
		exit(1);
	}

	echo "status(DP_TESTING_MODE_ENABLED)";
	exit(0);
}