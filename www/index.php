<?php

########################################################################
# Path to the main DeskPRO directory
########################################################################

$deskpro_dir = __DIR__.'/../';

########################################################################
# Do not edit below this line
########################################################################

$deskpro_dir = realpath($deskpro_dir);

if (!$deskpro_dir
    || !is_dir($deskpro_dir)
    || !file_exists($deskpro_dir . '/app/run/targets/web.php')
) {
    echo 'The $deskpro_dir configuration variable is invalid.';
	echo 'Please edit index.php and correct the path.';
	exit(1);
}

define('DESKPRO_WWW_PATH', __DIR__);
require $deskpro_dir . '/app/run/targets/web.php';










/*
 * START: VFS report, @todo remove before merge
 */
$vfsReport = $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' -> '.print_r(array_map(function ($item) {
    return $item[0];
}, \DpRun\DpFsProxyStreamWrapper::getCache()['kernel_cache'] ?? []), true);

@file_put_contents(sys_get_temp_dir().'/vfs-report', $vfsReport, FILE_APPEND);

/*
 * END: VFS report, @todo remove before merge
 */
