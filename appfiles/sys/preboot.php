<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

require DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';

$errors = array();

if (!deskpro_install_check_version()) {
	$errors[] = sprintf("The version of PHP you have (%s) is too old. DeskPRO requires PHP v5.3.2 or newer. You need to upgrade your version.", phpversion());
}

if (!deskpro_install_check_safemode()) {
	$errors[] = "PHP currently has <code>safe_mode</code> enabled. DeskPRO requires safe_mode to be set to \"Off\". You need to edit your PHP configuration to make this change.";
}

if (!deskpro_install_check_config()) {
	$errors[] = sprintf("You do not have a configuration file. Copy <code>/config.new.php</code> to <code>/config.php</code> and edit the values within.");
	exit;
}

if (!deskpro_install_check_writable()) {
	$errors[] = sprintf("You must make <code>/appfiles/sys/cache</code> and <code>/appfiles/sys/logs</code> writable by the server.");
}

if ($errors) {
	$errors = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
	echo deskpro_install_basic_error($errors);
	exit;
}
unset($errors);
