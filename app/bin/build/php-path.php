<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

$php_path = null;

if (isset($DP_CONFIG['php_path']) && $DP_CONFIG['php_path']) {
    $php_path = $DP_CONFIG['php_path'];
} else {
    $config_file = __DIR__.'/../../config.php';
    if (file_exists($config_file)) {
        require_once $config_file;
    }

    if (isset($DP_CONFIG['php_path']) && $DP_CONFIG['php_path']) {
        $php_path = $DP_CONFIG['php_path'];
    }
}

if (!$php_path) {
    $php_paths = array('/usr/bin/php', '/usr/local/bin/php', '/usr/bin/php5', '/usr/local/bin/php5', 'C:\\php\\php.exe', 'C:\\php5\\php.exe');

    $paths = explode(PATH_SEPARATOR, getenv('PATH'));

    foreach ($paths as $path) {
        $php_executable = $path.DIRECTORY_SEPARATOR.'php';

        if (file_exists($php_executable) && is_file($php_executable)) {
            $php_paths = array($php_executable);
        }

        $php_executable = $path.DIRECTORY_SEPARATOR.'php.exe';

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
            echo "Detected possible PHP at $php_path, but it seems invalid. Edit /config.php with the specific path to the PHP CLI.\n";
            exit(1);
        }
    }

    if (!$php_path) {
        echo "We could not detect where your PHP CLI is. Edit /config.php with the specific path to the PHP CLI.\n";
        exit(1);
    }
}

define('DP_PHP_PATH', $php_path);
