<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

require DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';

if (!deskpro_install_check_version()) {
	echo deskpro_install_basic_error(sprintf("The version of PHP you have (%s) is too old. DeskPRO requires PHP v5.3.2 or newer.", phpversion()));
	exit;
}

if (!deskpro_install_check_config()) {
	echo deskpro_install_basic_error(sprintf("Before you begin you must copy <code>/config.new.php</code> to <code>/config.php</code> and edit the values within."));
	exit;
}

if (!deskpro_install_check_writable()) {
	echo deskpro_install_basic_error("Before you proceed, you must make <code>/appfiles/sys/cache</code> and <code>/appfiles/sys/logs</code> writable by the server.");
	exit;
}
