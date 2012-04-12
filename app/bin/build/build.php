#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

chdir(__DIR__);
require './php-path.php';
define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../'));

$htaccess_path = realpath(DP_ROOT . '/../.htaccess');
if (is_file($htaccess_path) && is_writable($htaccess_path)) {
	$htaccess_contents = file_get_contents($htaccess_path);
	file_put_contents($htaccess_path, "Order allow,deny\nAllow from none\nDeny from all\n");
} else {
	$htaccess_path = false;
}

require DP_ROOT . '/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\ClassLoader\ClassCollectionLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Symfony' => DP_ROOT.'/vendor/symfony/src'));
$loader->register();

$output_realtime = function($type, $buffer) {
	if ($type === 'err') {
		echo 'ERR: '.$buffer;
	} else {
		echo $buffer;
	}
};

#####################################################################

$time = microtime(true);
echo "build-boostrap ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-bootstrap.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-kernels ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-kernels.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-caches ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-caches.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-assetic ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-assetic.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-compiled ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-compiled.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-schema-file ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-schema-file.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-template-map ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-template-map.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

// must do before checksum file is built

$build_time = time();
echo "echoing build time of ... $build_time ";

$proc = new \Symfony\Component\Process\Process("echo '<?php define(\"DP_BUILD_TIME\", $build_time); ' > build-time.php", DP_ROOT.'/sys/config');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE ";
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-cleanup ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-cleanup.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

if ($htaccess_path && $htaccess_contents) {
	file_put_contents($htaccess_path, $htaccess_contents);
}

#####################################################################

$time = microtime(true);
echo "build-checkphp ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-checkphp.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-checksum-file ... ";

$proc = new \Symfony\Component\Process\Process(DP_PHP_PATH . ' ./build-checksum-file.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run($output_realtime);

if (!$proc->isSuccessful()) {
	echo ("\nDetected error. Quitting.\n");
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

if ($htaccess_path && $htaccess_contents) {
	file_put_contents($htaccess_path, $htaccess_contents);
}
