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

require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT . '/bin/build/php-path.php';
require DP_ROOT.'/sys/system.php';

$paths = array(
	DP_ROOT.'/languages',
	DP_ROOT.'/bin',
	DP_ROOT.'/src',
	DP_ROOT.'/sys'
);

echo "Checking files for PHP errors\n";
$x = 0;
foreach ($paths as $dir) {

	$finder = new \Symfony\Component\Finder\Finder();
	$finder->files()->name('*.php')->in($dir);

	$has_failed = array();
	$bad_size = array();
	foreach ($finder as $file) {
		/** @var \Symfony\Component\Finder\SplFileinfo $file */
		$filepath = $file->getRealPath();

		$cmd = DP_PHP_PATH . " -l \"" . $file->getRealPath() . "\"";

		$out = null;
		exec($cmd, $out, $ret);

		if ($ret) {
			echo "\n";
			echo implode("\n", $out);
			echo "\n";
			$has_failed[] = str_replace(DP_ROOT, '', $file->getRealPath());
		} elseif (filesize($file->getRealPath()) == 4096) {
			$bad_size[] = str_replace(DP_ROOT, '', $file->getRealPath());
		} else {
			$x++;
			if ($x % 10 === 0) {
				echo ".";
			}
			if ($x % 100 == 0) {
				echo $x;
			}
		}
	}
}

echo "\n";

if ($has_failed) {
	echo "There were syntax errors detected in the following files:\n";
	echo "- " . implode("\n- ", $has_failed);
	echo "\n";
	exit(1);
}

if ($bad_size) {
	echo "The following files are susceptible to the magic 4096 bug (https://bugs.php.net/bug.php?id=60998):\n";
	echo "- " . implode("\n- ", $has_failed);
	echo "\n";
	exit(1);
}

if (!$has_failed && !$bad_size) {
	echo "No errors detected.\n";
}
exit(0);