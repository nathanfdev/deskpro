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

require DP_ROOT . '/sys/load_config.php';
require DP_ROOT . '/vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
$loader = new UniversalClassLoader();
$loader->register();

$finder = new \Symfony\Component\Finder\Finder();
$it = $finder->files()
		     ->in(DP_ROOT)
		     ->notName('distro-checksums.php')
		     ->exclude(DP_ROOT.'/sys/cache/dev')
		     ->exclude(dp_get_cache_dir().'/cache/dev');

$hashes = array();

foreach ($it as $file) {

}
