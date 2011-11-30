#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));

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
$proc->run();

if (!$proc->isSuccessful()) {
	echo "ERROR\n";
	echo $proc->getOutput();
	echo $proc->getErrorOutput();
	exit($proc->getExitCode());
}

echo " DONE " . sprintf("%.f", microtime(true)-$time);
echo "\n";
