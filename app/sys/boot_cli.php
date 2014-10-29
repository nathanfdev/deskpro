<?php if (!defined('DP_ROOT')) exit('No access');
require DP_ROOT . '/sys/KernelBooter.php';
define('USER_INTERFACE', 'user');
$kernel = defined('CLI_BOOT_KERNEL')  ? CLI_BOOT_KERNEL : null;
$return = \DeskPRO\Kernel\KernelBooter::bootCli('prod', false, $kernel);
\DeskPRO\Kernel\KernelBooter::DeskPRO_Done();
exit($return);