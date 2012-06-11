<?php if (!defined('DP_ROOT')) exit('No access');

$is_authed = false;
if (isset($_GET['_']) && file_exists(DP_CONFIG_FILE)) {
	$is_authed = (md5_file(DP_CONFIG_FILE) == $_GET['_']);
}

switch ($_GET['_sys']) {
	case 'blank':
		break;

	case 'memtest':
		if (!$is_authed) die('Invalid auth code.');
		require DP_ROOT . '/sys/scripts/memtest.php';
		break;

	case 'check':
		require DP_ROOT . '/sys/scripts/check.php';
		break;

	case 'phpinfo':
		require DP_ROOT . '/sys/scripts/phpinfo.php';
		break;

	case 'checkurl':
		require DP_ROOT . '/sys/scripts/checkurl.php';
		break;

	case 'checkurlpath':
		require DP_ROOT . '/sys/scripts/checkurlpath.php';
		break;

	case 'dev_run_migrations':
		if (!$is_authed) die('Invalid auth code.');
		require DP_ROOT . '/sys/scripts/dev_run_migrations.php';
		break;
}
