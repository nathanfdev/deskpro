#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    echo "This script must only be run from the CLI.\n";
    echo "Contact support@deskpro.com if you require assistance.\n";
    exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__.'/../../'));
define('DP_WEB_ROOT', realpath(__DIR__.'/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT.'/config.php');

require DP_ROOT.'/sys/autoload.php';

use Symfony\Component\ClassLoader\ClassCollectionLoader;

$bootstrap_file = DP_ROOT.'/sys/bootstrap.php';
if (file_exists($bootstrap_file)) {
    unlink($bootstrap_file);
}

$files = array(
    'Symfony\\Component\\DependencyInjection\\ContainerAwareInterface',
    // Cannot be included because annotations will parse the big compiled class file
    //'Symfony\\Component\\DependencyInjection\\ContainerAware',
    'Symfony\\Component\\DependencyInjection\\ContainerInterface',
    'Symfony\\Component\\DependencyInjection\\Container',
    'Symfony\\Component\\HttpKernel\\HttpKernelInterface',
    'Symfony\\Component\\HttpKernel\\KernelInterface',
    'Symfony\\Component\\HttpKernel\\Kernel',
    'Symfony\\Component\\ClassLoader\\ClassCollectionLoader',
    'Symfony\\Component\\ClassLoader\\UniversalClassLoader',
    'Symfony\\Component\\HttpKernel\\Bundle\\Bundle',
    'Symfony\\Component\\HttpKernel\\Bundle\\BundleInterface',
    'Symfony\\Component\\Config\\ConfigCache',
    // cannot be included as commands are discovered based on the path to this class via Reflection
    //'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
);

$finder = new \Symfony\Component\Finder\Finder();
$finder->in(DP_ROOT.'/src/Application/DeskPRO/DependencyInjection/SystemServices')->files()->name('*.php');
foreach ($finder as $file) {
    /* @var $file \SplFileInfo */
    $name = $file->getFilename();
    $name = str_replace('.php', '', $name);

    $files[] = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\'.$name;
}

ClassCollectionLoader::load($files, dirname($bootstrap_file), basename($bootstrap_file, '.php'), false, false, '.php');

$file = file_get_contents($bootstrap_file);
$file = str_replace('htmlspecialchars(', '@htmlspecialchars(', $file);
file_put_contents($bootstrap_file, "<?php\n\nnamespace { require_once DP_ROOT.'/sys/autoload.php'; }\n\n".substr($file, 5));
