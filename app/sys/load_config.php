<?php if (!defined('DP_ROOT')) exit('No access');
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Loads config. After this call, $DP_CONFIG is available.
 */
function dp_load_config()
{
	global $DP_CONFIG;
	static $has_loaded = false;

	if ($has_loaded) {
		return;
	}

	if (!is_array($DP_CONFIG)) {
		if (file_exists(DP_CONFIG_FILE)) {
			require DP_CONFIG_FILE;

			if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
				$DP_CONFIG = array();
			}

			if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
			if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = defined('DP_DATABASE_HOST')     ? DP_DATABASE_HOST     : 'localhost';
			if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = defined('DP_DATABASE_USER')     ? DP_DATABASE_USER     : 'YOUR_DATABASE_USER';
			if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = defined('DP_DATABASE_PASSWORD') ? DP_DATABASE_PASSWORD : 'YOUR_DATABASE_PASS';
			if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = defined('DP_DATABASE_NAME')     ? DP_DATABASE_NAME     : 'YOUR_DATABASE_NAME';
			if (!isset($DP_CONFIG['technical_email'])) $DP_CONFIG['technical_email'] = defined('DP_TECHNICAL_EMAIL')   ? DP_TECHNICAL_EMAIL   : '';
		} else {
			if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
				$DP_CONFIG = array();
				$DP_CONFIG['NO_CONFIG'] = true;
				$DP_CONFIG['db'] = array();
				$DP_CONFIG['db']['host']      = 'localhost';
				$DP_CONFIG['db']['user']      = 'YOUR_DATABASE_USER';
				$DP_CONFIG['db']['password']  = 'YOUR_DATABASE_PASS';
				$DP_CONFIG['db']['dbname']    = 'YOUR_DATABASE_NAME';
				$DP_CONFIG['technical_email'] = '';
			}
		}
	}

	if (!defined('DP_BUILD_TIME')) {
		if (file_exists(DP_ROOT.'/sys/config/build-time.php')) {
			require(DP_ROOT.'/sys/config/build-time.php');
		} else {
			define('DP_BUILD_TIME', 1323444089); // would be used by someone who hasnt built yet
		}
	}
}


/**
 * Get a value from config using dot notation
 *
 * @param string $key
 * @param null $default
 * @return mixed
 */
function dp_get_config($path, $default = null)
{
	global $DP_CONFIG;

	dp_load_config();
	$array = $DP_CONFIG;

	// If its not a path at all, we can do a simple lookup
	if (strpos($path, '.') === false) {
		return isset($array[$path]) ? $array[$path] : $default;
	}

	$parts = explode('.', $path);

	if (!$parts) {
		return $default;
	}

	while ($key = array_shift($parts)) {
		if (!isset($array[$key])) {
			return $default;
		}

		$array = $array[$key];
	}

	return $array;
}


/**
 * @return string
 */
function dp_get_log_dir()
{
	dp_load_config();

	global $DP_CONFIG;
	if (isset($DP_CONFIG['dir_logs']) && $DP_CONFIG['dir_logs']) {
		$log_dir = $DP_CONFIG['dir_logs'];
	} else {
		$log_dir = DP_WEB_ROOT . '/data/logs';
	}

	return $log_dir;
}


/**
 * @return string
 */
function dp_get_backup_dir()
{
	dp_load_config();

	global $DP_CONFIG;
	if (isset($DP_CONFIG['dir_backups']) && $DP_CONFIG['dir_backups']) {
		$backup_dir = $DP_CONFIG['dir_backups'];
	} else {
		$backup_dir = DP_WEB_ROOT . '/data/backups';
	}

	return $backup_dir;
}


/**
 * @return string
 */
function dp_get_blob_dir()
{
	dp_load_config();

	global $DP_CONFIG;
	if (isset($DP_CONFIG['dir_files']) && $DP_CONFIG['dir_files']) {
		$blob_dir = $DP_CONFIG['dir_files'];
	} else {
		$blob_dir = DP_WEB_ROOT . '/data/files';
	}

	return $blob_dir;
}