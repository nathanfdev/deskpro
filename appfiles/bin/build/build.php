#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));

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

#####################################################################

$time = microtime(true);
echo "build-boostrap ... ";

$proc = new \Symfony\Component\Process\Process('./build-bootstrap.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-kernels ... ";

$proc = new \Symfony\Component\Process\Process('./build-kernels.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-caches ... ";

$proc = new \Symfony\Component\Process\Process('./build-caches.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-assetic ... ";

$proc = new \Symfony\Component\Process\Process('./build-assetic.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-compiled ... ";

$proc = new \Symfony\Component\Process\Process('./build-compiled.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

$time = microtime(true);
echo "build-schema-file ... ";

$proc = new \Symfony\Component\Process\Process('./build-schema-file.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

#####################################################################

// must do before checksum file is built

$build_time = time();
echo "echoing build time of ... ";

$proc = new \Symfony\Component\Process\Process("echo '<?php define(\"DP_BUILD_TIME\", $build_time); ' > build-time.php", DP_ROOT.'/sys/config');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE ";
echo "\n";


#####################################################################

$time = microtime(true);
echo "build-checksum-file ... ";

$proc = new \Symfony\Component\Process\Process('./build-checksum-file.php', DP_ROOT.'/bin/build');
$proc->setTimeout(600);
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";

if ($htaccess_path && $htaccess_contents) {
	file_put_contents($htaccess_path, $htaccess_contents);
}
