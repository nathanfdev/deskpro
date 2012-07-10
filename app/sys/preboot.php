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
# See if we need to clear apc
#------------------------------

if (file_exists(dp_get_tmp_dir() . '/apc-clear.trigger')) {
	error_log("cleared");
	if (function_exists('apc_clear_cache')) {
		apc_clear_cache();
		apc_clear_cache('user');
	}
	@unlink(dp_get_tmp_dir() . '/apc-clear.trigger');
}

#------------------------------
# Attempt to set min memory limit to 128 MB
#------------------------------

define('DP_REAL_MEMSIZE', deskpro_install_check_parseinisize(@ini_get('memory_limit')));
$mem_size = DP_REAL_MEMSIZE;
if ($mem_size && $mem_size != '-1' && $mem_size < 134217728/* 128 MB */) {
	@ini_set('memory_limit', 134217728);
}
unset($mem_size);


#------------------------------
# Attempt to set max_execution_time to at least 40s
#------------------------------

define('DP_REAL_MAX_EXEC_TIME', @ini_get('max_execution_time'));
$max_time = DP_REAL_MAX_EXEC_TIME;
if (!$max_time || $max_time < 40) {
	@set_time_limit(40);
}
unset($max_time);

#------------------------------
# Attempt to set error log file if unset
#------------------------------

@ini_set('log_errors', true);

define('DP_REAL_ERROR_LOG', @ini_get('error_log'));
if (!DP_REAL_ERROR_LOG) {
	if (defined('DP_BOOT_MODE') && (DP_BOOT_MODE == 'cron' || DP_BOOT_MODE == 'cli')) {
		@ini_set('error_log', dp_get_log_dir() . '/server-phperr-cli.log');
	} else {
		@ini_set('error_log', dp_get_log_dir() . '/server-phperr-web.log');
	}
}

#------------------------------
# Handle CLI logging of info
#------------------------------

if ((defined('DP_BOOT_MODE') && DP_BOOT_MODE == 'cron') || (isset($_SERVER['argv']) && in_array('dp_write_cli_info', $_SERVER['argv']))) {

	$do_update = false;
	$last_error_log_hash = null;
	if (file_exists(dp_get_data_dir() .'/cli-server-reqs-check.dat')) {
		$data = file_get_contents(dp_get_data_dir() .'/cli-server-reqs-check.dat');
		$data = @unserialize($data);

		// Update these files every 5 minutes on cron
		if (!$data || !isset($data['gen_time']) || $data['gen_time'] < time() - 300) {
			$do_update = true;
		}

		if (isset($data['error_log_hash'])) {
			$last_error_log_hash = $data['error_log_hash'];
		}
	} else {
		$do_update = true;
	}

	if ($do_update) {
		ob_start();
		@phpinfo();
		$phpinfo = ob_get_clean();
		@file_put_contents(dp_get_data_dir() .'/cli-phpinfo.html', $phpinfo);
		@chmod(dp_get_data_dir() .'/cli-phpinfo.html', 0777);

		$data = array('checks' => deskpro_install_check_reqs());
		$data['gen_time'] = time();
		$data['php_version'] = phpversion();
		$data['memory_limit'] = deskpro_install_check_parseinisize(@ini_get('memory_limit'));
		$data['memory_limit_real'] = DP_REAL_MEMSIZE;
		$data['error_log'] = @ini_get('error_log');
		$data['error_log_real'] = DP_REAL_ERROR_LOG;

		if ($data['error_log'] && file_exists($data['error_log']) && is_readable($data['error_log'])) {
			$data['error_log_hash'] = md5_file($data['error_log']);

			if ($last_error_log_hash != $data['error_log_hash']) {
				@copy($data['error_log'], dp_get_log_dir() . '/cli-phperr.log');
				@chmod(dp_get_log_dir() . '/cli-phperr.log', 0777);
			}
		}

		@file_put_contents(dp_get_data_dir() .'/cli-server-reqs-check.dat', serialize($data));
		@chmod(dp_get_data_dir() .'/cli-server-reqs-check.dat', 0777);
	}

	unset($do_update, $phpinfo, $data);

	if (isset($_SERVER['argv']) && in_array('dp_write_cli_info', $_SERVER['argv'])) {
		exit;
	}
}

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

	if (!(defined('DP_BOOT_MODE') && DP_BOOT_MODE == 'cron')) {
		deskpro_install_simple_data_submit(implode("\n", $errors));
	}

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