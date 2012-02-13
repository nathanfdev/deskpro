<?php

/**
 * You can edit this line to set the path to the PHP CLI binary if you know it.
 */
$php_path = null;


########################################################################################################################



if (!$php_path) {
	if (isset($_SERVER['_']) && is_executable($_SERVER['_'])) {
		$php_path = $_SERVER['_'];
	} else {
		foreach (array('/usr/bin/php', '/usr/local/bin/php', '/usr/bin/php5', '/usr/local/bin/php5', 'C:\\php\\php.exe', 'C:\\php5\\php.exe') as $try) {
			if (is_executable($try)) {
				$php_path = $try;
			}
		}
	}

	if ($php_path) {
		exec("$php_path -v", $out);
		if (!isset($out[0]) || strpos($out[0], 'PHP 5.') === false) {
			echo "Detected possible PHP at $php_path, but it seems invalid. Edit /appfiles/bin/build/php-path.php with the specific path to the PHP binary.\n";
			exit(1);
		}
	}

	if (!$php_path) {
		echo "We could not detect where your PHP CLI binary is. Edit /appfiles/bin/build/php-path.php with the specific path to the PHP binary.\n";
		exit(1);
	}
}

define('DP_PHP_PATH', $php_path);
