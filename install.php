<?php
/**
 * The path to the 'appfiles' directory. If you want to move the directory,
 * you must update this path.
 */
define('DP_ROOT', __DIR__ . '/appfiles');

error_reporting(E_ALL);
ini_set('display_errors', true);

###############################################################################
# Do very basic checks here to make sure theres no "white page" problem
###############################################################################

require DP_ROOT.'/src/Application/InstallBundle/Install/server_check_functions.php';

if (!deskpro_install_check_version()) {
	echo deskpro_install_basic_error(sprintf("The version of PHP you have (%s) is too old. DeskPRO requires PHP v5.3.2 or newer.", phpversion()));
	exit;
}

if (!deskpro_install_check_config()) {
	echo deskpro_install_basic_error(sprintf("Before you begin you must copy <code>/appfiles/config.new.php</code> to <code>/appfiles/config.php</code> and edit the values within."));
	exit;
}

if (!deskpro_install_check_writable()) {
	echo deskpro_install_basic_error("Before you proceed, you must make <code>/appfiles/src/cache</code> and <code>/appfiles/src/logs</code> writable by the server.");
	exit;
}

###############################################################################
# Boot up the install kernel
###############################################################################

require DP_ROOT . '/sys/bootstrap-dev.php';

$kernel_class = 'DeskPRO\\Kernel\\InstallKernel';
define('DP_INTERFACE', 'install');

$request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();

$kernel = new $kernel_class('prod', true);
$kernel->handle($request)->send();
