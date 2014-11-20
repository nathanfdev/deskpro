#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    echo "This script must only be run from the CLI.\n";
    echo "Contact support@deskpro.com if you require assistance.\n";
    exit(1);
}

chdir(__DIR__);

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__.'/../../'));
define('DP_WEB_ROOT', realpath(__DIR__.'/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT.'/config.php');

require DP_ROOT.'/bin/build/inc.php';
require_once DP_ROOT.'/sys/load_config.php';
require DP_ROOT.'/bin/build/php-path.php';

$proc_kernel = null;
if (($k = array_search('--knum', $_SERVER['argv'])) !== false) {
    if (isset($_SERVER['argv'][$k+1])) {
        $proc_kernel = $_SERVER['argv'][$k+1];
    }
}

$kernel_classes = array(
    'DeskPRO\\Kernel\\DpKernel',
    'DeskPRO\\Kernel\\InstallKernel',
    'DeskPRO\\Kernel\\PortalKernel',
);

if ($proc_kernel === null) {
    $cache_dir = dp_get_cache_dir();
    echo "Removing existing caches dir $cache_dir ... ";

    $proc = new Symfony\Component\Process\Process('rm -rf portal dev prod doctrine-proxies prod twig-compiled annotations.php', dp_get_cache_dir());
    $proc->run();
    if (!$proc->isSuccessful()) {
        echo "ERROR\n\n";
        $proc->getOutput();
        $proc->getErrorOutput();
        exit($proc->getExitCode());
    }
    echo "Done\n";

    foreach ($kernel_classes as $k => $kernel_class) {
        echo "Building {$kernel_class} ... ";
        $time = microtime(true);

        $cmd  = DP_PHP_PATH.' ./build-kernels.php --knum '.$k;
        $proc = new Symfony\Component\Process\Process($cmd, DP_ROOT.'/bin/build');
        $proc->run(function ($type, $buffer) {
            if ($type === 'err') {
                echo 'ERR: '.$buffer;
            } else {
                echo $buffer;
            }
        });

        if (!$proc->isSuccessful()) {
            exit($proc->getExitCode());
        }

        printf(" Done %.fs\n", (microtime(true) - $time));
    }

    echo "\nDone";

    exit(0);
} else {
    require_once DP_ROOT.'/sys/system.php';

    $class  = $kernel_classes[$proc_kernel];
    $kernel = new $class('prod', false);
    $kernel->boot();

    exit(0);
}
