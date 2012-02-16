<?php
require DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';


#------------------------------
# Attempt to set min memory limit to 128 MB
#------------------------------

$mem_size = @ini_get('memory_limit');
if ($mem_size && $mem_size != '-1' && deskpro_install_check_parseinisize($mem_size) < 134217728/* 128 MB */) {
	@ini_set('memory_limit', 134217728);
}
unset($mem_size);


#------------------------------
# Attempt to set max_execution_time to at least 40s
#------------------------------

$max_time = @ini_get('max_execution_time');
if (!$max_time || $max_time < 40) {
	@set_time_limit(40);
}
unset($max_time);

#------------------------------
# Run low-level server checks
#------------------------------

$errors = array();

if (!deskpro_install_check_version()) {
	$errors[] = sprintf("The version of PHP you have (%s) is too old. DeskPRO requires PHP v5.3.2 or newer. You need to upgrade your version.", phpversion());
}

if (!deskpro_install_check_safemode()) {
	$errors[] = "PHP currently has <code>safe_mode</code> enabled. DeskPRO requires safe_mode to be set to \"Off\". You need to edit your PHP configuration to make this change.";
}

if (!deskpro_install_check_config()) {
	$errors[] = sprintf("You do not have a configuration file. Copy <code>/config.new.php</code> to <code>/config.php</code> and edit the values within.");
}

if (!deskpro_install_check_writable()) {
	$errors[] = sprintf("You must make <code>/app/sys/cache</code> and <code>/app/sys/logs</code> writable by the server.");
}

if ($errors) {
	$errors = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
	echo deskpro_install_basic_error($errors);
	exit;
}
unset($errors);
