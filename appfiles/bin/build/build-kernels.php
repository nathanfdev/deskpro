#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_ROOT', realpath(__DIR__ . '/../../'));
require DP_ROOT . '/bin/build/inc.php';

$proc_kernel = null;
if (($k = array_search('--knum', $_SERVER['argv'])) !== false) {
	if (isset($_SERVER['argv'][$k+1])) {
		$proc_kernel = $_SERVER['argv'][$k+1];
	}
}

$kernel_classes = array(
	'DeskPRO\\Kernel\\AgentKernel',
	'DeskPRO\\Kernel\\CliKernel',
	'DeskPRO\\Kernel\\ReportKernel',
	'DeskPRO\\Kernel\\UserKernel',
	'DeskPRO\\Kernel\\SysKernel',
	'DeskPRO\\Kernel\\InstallKernel',
);

if ($proc_kernel === null) {
	$cache_dir = DP_ROOT.'/sys/cache/prod';
	if (file_exists($cache_dir)) {
		echo "Removing existing cache dir ... ";
		$proc = new Symfony\Component\Process\Process('rm -rf prod', DP_ROOT.'/sys/cache');
		$proc->run();
		if (!$proc->isSuccessful()) {
			echo "ERROR\n\n";
			$proc->getOutput();
			$proc->getErrorOutput();
			exit($proc->getExitCode());
		}
		echo "Done\n";
	}

	foreach ($kernel_classes as $k => $kernel_class) {
		echo "Building {$kernel_class} ... ";
		$time = microtime(true);

		$cmd = './build-kernels.php --knum ' . $k;
		$proc = new Symfony\Component\Process\Process($cmd, DP_ROOT.'/bin/build');
		$proc->run();

		if (!$proc->isSuccessful()) {
			echo "ERROR\n\n";
			echo $proc->getOutput();
			echo $proc->getErrorOutput();
			exit($proc->getExitCode());
		}

		printf(" Done %.fs\n", (microtime(true) - $time));
	}

	echo "\nDone";

	exit(0);
} else {

	require DP_ROOT.'/sys/system.php';

	$class = $kernel_classes[$proc_kernel];
	$kernel = new $class('prod', true);
	$kernel->boot();
	exit(0);
}
