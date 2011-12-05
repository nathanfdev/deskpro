#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));
require DP_ROOT . '/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('Symfony' => DP_ROOT.'/vendor/symfony/src'));
$loader->register();

$finder = new \Symfony\Component\Finder\Finder();
$finder->files()
		     ->in(DP_ROOT)
		     ->notName('distro-checksums.php')
		     ->notName('.gitignore')
		     ->notName('.DS_Store')
		     ->notName('dev_debug.php')
		     ->notName('config.php')
			 ->ignoreVCS(true)
		     ->exclude('sys/cache/dev');

foreach ($dirs as $d) {
	$finder->exclude($d);
}

$it = $finder->getIterator();

$hashes = array();
$count = 0;

$start = microtime(true);
echo "Starting at " . sprintf("%.f", $start) . "\n";

foreach ($it as $file) {

	$count++;
	if ($count && $count % 100 == 0) {
		echo "Processed $count files...\n";
	}

	$path = str_replace(DP_ROOT, '', $file->getRealPath());
	$hashes[$path] = md5_file($file->getRealPath());
}

$php = '<?php return ' . var_export($hashes, true) . ";\n";

file_put_contents(DP_ROOT.'/sys/distro-checksums.php', $php);

$end = microtime(true);
echo sprintf("\nDone $count files in %.f seconds\n", $end-$start);
