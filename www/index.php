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
