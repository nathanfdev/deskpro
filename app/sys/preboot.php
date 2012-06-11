<?php if (!defined('DP_ROOT')) exit('No access');

#------------------------------
# Normalize env
#------------------------------

@setlocale(LC_CTYPE, 'C');
@date_default_timezone_set('UTC');
@ini_set('default_charset', 'UTF-8');

require DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';
require DP_ROOT . '/sys/load_config.php';
dp_load_config();


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
$errors_codes = array();

if (!deskpro_install_check_version()) {
	$errors[] = "The version of PHP you have is too old. DeskPRO requires PHP v5.3.2 or newer but you are using " . phpversion() . ". You need to upgrade your version.";
	$errors_codes[] = 'php_version';
}

if (!deskpro_install_check_pcre()) {
	$errors[] = "PHP is configured with a `pcre.backtrack_limit` value that is too low. Edit your php.ini configuration and change it to at least 100000.";
	$errors_codes[] = 'pcre_backtrack_limit';
}

if (!deskpro_install_check_safemode()) {
	$errors[] = "PHP currently has <code>safe_mode</code> enabled. DeskPRO requires safe_mode to be set to \"Off\". You need to edit your PHP configuration to make this change.";
	$errors_codes[] = 'safe_mode';
}

if (php_sapi_name() == 'cli') {
	if (!deskpro_install_check_pdo()) {
		$errors[] = "PHP on the command-line does not have PDO installed. It is possible you have to install 'pdo' into a separate php.ini file (noted below) for command-line usage.";
		$errors_codes[] = 'pdo_ext';
	} elseif (!deskpro_install_check_pdo()) {
		$errors[] = "PHP on the command-line has PDO installed, but not the MySQL driver. It is possible you have to install 'pdo_mysql' into a separate php.ini file (noted below) for command-line usage.";
		$errors_codes[] = 'pdo_mysql_ext';
	}
}

if ($errors) {

	deskpro_install_simple_data_submit(implode("\n", $errors));

	if (php_sapi_name() == 'cli') {
		$msg = "There are problems with your server that prevent DeskPRO from executing this command:\n\n";
		$msg .= '- ' . implode("\n- ", $errors);
		$msg .= "\n\n";

		$ini_path = deskpro_install_guess_phpini_path();

		$msg .= "The path to php.ini that is being use on the command-line:\n" . $ini_path . "\n\n";

		if (defined('DP_BOOT_MODE') && DP_BOOT_MODE == 'cron') {
			$msg_codes = array();
			foreach ($errors_codes as $e) {
				$msg_codes[] = 'error: ' . $e;
			}

			if ($ini_path) {
				$msg_codes[] = "ini_path: $ini_path";
			}
			@file_put_contents(dp_get_log_dir().'/cron-boot-errors.log', $msg . "###\n\n" . implode("\n", $msg_codes));
		}

		echo $msg;
	} else {
		$errors = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
		echo deskpro_install_basic_error($errors);
	}
	exit;
}
unset($errors);
