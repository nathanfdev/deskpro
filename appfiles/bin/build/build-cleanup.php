#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));

// Remove log stuff
$rm_paths = array(
	DP_ROOT.'/sys/cache/prod/admin/AdminKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/admin/AdminKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/admin/AdminKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/agent/AgentKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/agent/AgentKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/agent/AgentKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/cli/CliKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/cli/CliKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/cli/CliKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/install/InstallKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/install/InstallKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/install/InstallKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/report/ReportKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/report/ReportKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/report/ReportKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/sys/SysKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/sys/SysKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/sys/SysKernelDebugContainerCompiler.log',

	DP_ROOT.'/sys/cache/prod/user/UserKernelDebugContainer.php.meta',
	DP_ROOT.'/sys/cache/prod/user/UserKernelDebugContainer.xml',
	DP_ROOT.'/sys/cache/prod/user/UserKernelDebugContainerCompiler.log',
);

foreach ($rm_paths as $p) {
	if (file_exists($p)) {
		unlink($p);
	}
}
