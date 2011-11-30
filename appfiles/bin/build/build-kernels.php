#!/usr/bin/env php
<?php
define('DP_ROOT', realpath(__DIR__ . '/../../'));
require(DP_ROOT . '/sys/bootstrap-dev.php');

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
	$class = $kernel_classes[$proc_kernel];
	$kernel = new $class('prod', false);
	$kernel->boot();
}
