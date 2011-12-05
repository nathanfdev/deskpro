#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));
require DP_ROOT . '/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Symfony' => DP_ROOT.'/vendor/symfony/src'));
$loader->register();

$finder = new \Symfony\Component\Finder\Finder();
$it = $finder->files()
		     ->in(DP_ROOT)
		     ->notName('distro-checksums.php')
		     ->exclude(DP_ROOT.'/sys/cache/dev');

$hashes = array();

foreach ($it as $file) {

}
