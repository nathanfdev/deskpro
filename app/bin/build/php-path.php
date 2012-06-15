<?php

/**
 * You can edit this line to set the path to the PHP CLI if you know it.
 */
$php_path = null;


########################################################################################################################



if (!$php_path) {
	$php_paths = array('/usr/bin/php', '/usr/local/bin/php', '/usr/bin/php5', '/usr/local/bin/php5', 'C:\\php\\php.exe', 'C:\\php5\\php.exe');

	$paths = explode(PATH_SEPARATOR, getenv('PATH'));

	foreach ($paths as $path) {
		$php_executable = $path . DIRECTORY_SEPARATOR . "php";

		if (file_exists($php_executable) && is_file($php_executable)) {
			$php_paths = array($php_executable);
		}

		$php_executable = $path . DIRECTORY_SEPARATOR . "php.exe";

		if (file_exists($php_executable) && is_file($php_executable)) {
			$php_paths = array($php_executable);
		}
	}

	if (isset($_SERVER['_']) && is_executable($_SERVER['_'])
	&& basename($_SERVER['SCRIPT_FILENAME']) != basename($_SERVER['_'])) {
		$try[] = $_SERVER['_'];
	}

	foreach ($php_paths as $try) {
		if (is_executable($try)) {
			$php_path = $try;
		}
	}

	if ($php_path) {
		exec("$php_path -v", $out);
		if (!isset($out[0]) || strpos($out[0], 'PHP 5.') === false) {
			echo "Detected possible PHP at $php_path, but it seems invalid. Edit /appfiles/bin/build/php-path.php with the specific path to the PHP CLI.\n";
			exit(1);
		}
	}

	if (!$php_path) {
		echo "We could not detect where your PHP CLI is. Edit /appfiles/bin/build/php-path.php with the specific path to the PHP CLI.\n";
		exit(1);
	}
}

define('DP_PHP_PATH', $php_path);
