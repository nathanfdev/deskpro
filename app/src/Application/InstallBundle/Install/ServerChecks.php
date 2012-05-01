<?php
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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 */

namespace Application\InstallBundle\Install;

use Application\DeskPRO\DBAL\Connection;
use Orb\Log\Logger;

use Application\DeskPRO\App;

class ServerChecks
{
	const MODE_NORMAL = 'normal';
	const MODE_CRON = 'cron';

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger = null;

	/**
	 * @var array
	 */
	protected $server_errors = array();

	/**
	 * @var bool
	 */
	protected $has_fatal_server_errors = false;

	/**
	 * @var bool
	 */
	protected $has_fatal_db_errors = false;

	/**
	 * @var string
	 */
	protected $mode = 'normal';

	/**
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Set check mode
	 *
	 * @param string $mode
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}

	protected function getLogger()
	{
		if ($this->logger === null) {
			$this->logger = new \Orb\Log\Logger();
		}

		return $this->logger;
	}


	/**
	 * Are there any errors?
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		if ($this->server_errors) {
			return true;
		}

		return false;
	}


	/**
	 * Are there any fatal errors?
	 *
	 * @return bool
	 */
	public function hasFatalErrors()
	{
		foreach ($this->server_errors as $e) {
			if ($e['level'] == 'fatal') {
				return true;
			}
		}

		return false;
	}


	/**
	 * Are there any fatal server (pre-db checks) errors?
	 *
	 * @return bool
	 */
	public function hasFatalServerErrors()
	{
		return $this->has_fatal_server_errors;
	}


	/**
	 * Are there any fatal DB (post server) errors?
	 *
	 * @return bool
	 */
	public function hasFatalDbErrors()
	{
		return $this->has_fatal_db_errors;
	}


	/**
	 * Are there any non-fatal errors?
	 *
	 * @return bool
	 */
	public function hasNonFatalErrors()
	{
		foreach ($this->server_errors as $e) {
			if ($e['level'] != 'fatal') {
				return true;
			}
		}

		return false;
	}


	/**
	 * Check if a speciifc error occurred
	 *
	 * @param string $type
	 * @return bool
	 */
	public function hasErrorType($type)
	{
		return isset($this->server_errors[$type]);
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->server_errors;
	}


	/**
	 * Get only fatal errors
	 *
	 * @return array
	 */
	public function getFatalErrors()
	{
		$ret = array();
		foreach ($this->server_errors as $k => $e) {
			if ($e['level'] == 'fatal') {
				$ret[$k] = $e;
			}
		}

		return $ret;
	}


	/**
	 * Get only non-fatal errors
	 *
	 * @return array
	 */
	public function getNonFatalErrors()
	{
		$ret = array();
		foreach ($this->server_errors as $e) {
			if ($e['level'] != 'fatal') {
				$ret[] = $e;
			}
		}

		return $ret;
	}


	/**
	 * @return array
	 */
	public function getErrorTypes()
	{
		return array_keys($this->server_errors);
	}


	/**
	 * Runs through basic server checks
	 *
	 * @return bool True if all okay, or false if there are errors
	 */
	public function checkServer($type = 'all')
	{
		#------------------------------
		# php_version
		#------------------------------

		if ($type == 'php_version' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking PHP version >= 5.3.2", Logger::DEBUG);
			if (deskpro_install_check_version()) {
				$this->getLogger()->log("[OK] PHP version of " . phpversion() . " is OK", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "[FATAL] Install PHP 5.3.2 or newer. You currently have " . phpversion();
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['php_version'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);

				// Lets not go any further in case the php version is so old something in this script fails
				return false;
			}
		}

		#------------------------------
		# config
		#------------------------------

		if ($type == 'config' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking config file", Logger::DEBUG);
			if (file_exists(DP_CONFIG_FILE)) {
				require_once(DP_CONFIG_FILE);

				if (!defined('DP_DATABASE_HOST') || !defined('DP_DATABASE_USER') || !defined('DP_DATABASE_PASSWORD') || !defined('DP_DATABASE_NAME')) {
					$this->has_fatal_server_errors = true;
					$msg = "/config.php exists but does not contain the required database values";
					$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
					$this->server_errors['config_values'] = array(
						'message' => $msg,
						'level' => 'fatal'
					);
				} else {
					$this->getLogger()->log("[OK] config file exists and contains required values", Logger::DEBUG);
				}
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "/config.php file is missing";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['config'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# json_ext
		#------------------------------

		if ($type == 'json_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking for json extension", Logger::DEBUG);
			if (function_exists('json_encode')) {
				$this->getLogger()->log("[OK] json extension installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the json extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['json_ext'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# session_ext
		#------------------------------

		if ($type == 'session_ext' || ($type == 'all' && $this->mode != 'cron')) {
			$this->getLogger()->log("[CHECK] Checking for session extension", Logger::DEBUG);
			if (function_exists('session_start')) {
				$this->getLogger()->log("[OK] session extension installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the session extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['session_ext'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# image_manip
		#------------------------------

		if ($type == 'image_manip' || ($type == 'all' && $this->mode != 'cron')) {
			$this->getLogger()->log("[CHECK] Checking for an image manipulation extension", Logger::DEBUG);
			if (deskpro_install_check_image_manip()) {
				$this->getLogger()->log("[OK] An image manipulation extension is installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the Imagick, Gmagick or GD extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['image_manip'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# ctype_ext
		#------------------------------

		if ($type == 'ctype_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking for ctype extension", Logger::DEBUG);
			if (function_exists('ctype_alpha')) {
				$this->getLogger()->log("[OK] ctype session installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the ctype extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['ctype_ext'] = array(
					'message' => "Install and enable the ctype extension",
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# tokenizer_ext
		#------------------------------

		if ($type == 'tokenizer_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking for tokenizer extension", Logger::DEBUG);
			if (function_exists('token_get_all')) {
				$this->getLogger()->log("[OK] tokenizer session installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the tokenizer extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['tokenizer_ext'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# pdo_ext
		#------------------------------

		if ($type == 'pdo_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking for PDO extension", Logger::DEBUG);
			if (deskpro_install_check_pdo()) {
				$this->getLogger()->log("[OK] PDO installed", Logger::DEBUG);

				$this->getLogger()->log("[CHECK] Checking for PDO_MySQL", Logger::DEBUG);
				if (deskpro_install_check_pdo_mysql()) {
					$this->getLogger()->log("[OK] PDO_MySQL installed", Logger::DEBUG);
				} else {
					$this->has_fatal_server_errors = true;
					$msg = "You need to install the MySQL PDO driver";
					$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
					$this->server_errors['pdo_mysql_ext'] = array(
						'message' => $msg,
						'level' => 'fatal'
					);
				}
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "Install and enable the PDO/PDO_MySQL extension";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['pdo_ext'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# openssl
		#------------------------------

		if ($type == 'openssl_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking if the OpenSSL extension is enabled", Logger::DEBUG);
			if (extension_loaded('openssl')) {
				$this->getLogger()->log("[OK] OpenSSL installed", Logger::DEBUG);
			} else {
				$msg = "We recommend installing the OpenSSL extension so you can use resources that require a secure connection such as Google Apps, Facebook and Twitter.";
				$this->getLogger()->log("$msg", Logger::INFO);
				$this->server_errors['openssl_ext'] = array(
					'message' => $msg,
					'level' => 'recommended'
				);
			}
		}

		#------------------------------
		# apc_check
		#------------------------------

		if ($type == 'apc_check' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking if APC is enabled", Logger::DEBUG);
			if (function_exists('apc_store') && ini_get('apc.enabled')) {
				$this->getLogger()->log("[OK] APC store installed", Logger::DEBUG);
			} else {
				$msg = "We recommend installing the APC extension for PHP to dramatically improve performance";
				$this->getLogger()->log("$msg", Logger::INFO);
				$this->server_errors['apc_check'] = array(
					'message' => $msg,
					'level' => 'recommended'
				);
			}
		}

		#------------------------------
		# magic_quotes_check
		#------------------------------

		if (function_exists('get_magic_quotes_gpc')) {
			if ($type == 'magic_quotes_gpc_check' || $type == 'all') {
				$this->getLogger()->log("[CHECK] Checking if magic_quotes_gpc is enabled", Logger::DEBUG);
				if (!get_magic_quotes_gpc()) {
					$this->getLogger()->log("[OK] magic_quotes_gpc is disabled", Logger::DEBUG);
				} else {
					$msg = "We recommend disabling the `magic_quotes_gpc` setting in your php.ini file.";
					$this->getLogger()->log("$msg", Logger::INFO);
					$this->server_errors['magic_quotes_gpc_check'] = array(
						'message' => $msg,
						'level' => 'recommended'
					);
				}
			}
		}

		#------------------------------
		# iconv_ext
		#------------------------------

		if ($type == 'iconv_ext' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking iconv is installed", Logger::DEBUG);
			if (function_exists('iconv')) {
				$this->getLogger()->log("[OK] iconv is installed", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "You must install and enabled the iconv extension";
				$this->getLogger()->log("$msg", Logger::INFO);
				$this->server_errors['iconv_ext'] = array(
					'message' => $msg,
					'level' => 'recommended'
				);
			}
		}

		#------------------------------
		# memory_limit
		#------------------------------

		if ($type == 'memory_limit' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking memory limit", Logger::DEBUG);
			if (deskpro_install_check_memory_limit()) {
				$this->getLogger()->log("[OK] Memory limit is okay", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "DeskPRO needs PHP's memory_limit option to be at least 128 MB";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['memory_limit'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		#------------------------------
		# logs_write
		#------------------------------

		if ($type == 'logs_write' || $type == 'all') {
			$this->getLogger()->log("[CHECK] Checking if logs dir is writable", Logger::DEBUG);
			$dir = App::getKernel()->getUserLogDir();
			if (is_dir($dir) && is_writable($dir)) {
				$this->getLogger()->log("[OK] Logs dir is writable", Logger::DEBUG);
			} else {
				$this->has_fatal_server_errors = true;
				$msg = "The " . str_replace(DP_WEB_ROOT, '', $dir) . " directory must exist and be writable";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['logs_write'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);
			}
		}

		if ($this->server_errors) {
			return false;
		}

		return true;
	}


	/**
	 * Checks the database to make sure details are correct and version etc is ok
	 *
	 * @param array $db_conf
	 * @return bool
	 */
	public function checkDatabase(array $db_conf)
	{
		$db_conf['driver'] = 'pdo_mysql';

		// Dont attempt check if theres a PDO failure
		if ($this->hasErrorType('pdo_ext') || $this->hasErrorType('pdo_mysql_ext')) {
			return;
		}

		$this->getLogger()->log("[CHECK] Checking database connection", Logger::DEBUG);
		try {
			$db = \Doctrine\DBAL\DriverManager::getConnection($db_conf);
			$db->connect();
		} catch (\Exception $e) {
			$this->has_fatal_db_errors = true;
			$msg = "Connection failed: {$e->getMessage()}";
			$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
			$this->server_errors['db_connect'] = array(
				'message' => $msg,
				'level' => 'fatal'
			);

			return false;
		}

		if ($this->mode != 'cron') {
			$this->getLogger()->log("[CHECK] checking for innodb engine", Logger::DEBUG);
			$engines = App::getDb()->fetchAllKeyed("SHOW ENGINES", array(), 'Engine');
			if (!$engines || !isset($engines['InnoDB']) || $engines['InnoDB']['Support'] == 'NO') {
				$this->has_fatal_db_errors = true;
				$msg = "MySQL does not have the InnoDB engine enabled";
				$this->getLogger()->log("[FAIL] $msg", Logger::INFO);
				$this->server_errors['db_no_innodb'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);

				return false;
			} else {
				$this->getLogger()->log("[OK] innodb engine enabled", Logger::DEBUG);
			}

			$this->getLogger()->log("[CHECK] Checking mysql version is >= 5.0", Logger::DEBUG);
			$ver = $db->fetchColumn("SHOW VARIABLES LIKE 'version'", array(), 1);
			if (version_compare($ver, '5.0', '>=')) {
				$this->getLogger()->log("[OK] mysql version is okay", Logger::DEBUG);
			} else {
				$this->has_fatal_db_errors = true;
				$msg = "Install MySQL verson 5.0 or newer";
				$this->getLogger()->log("[FATAL] $msg", Logger::INFO);
				$this->server_errors['db_version'] = array(
					'message' => $msg,
					'level' => 'fatal'
				);

				return false;
			}
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function hasDbErrors()
	{
		foreach ($this->server_errors as $k => $info) {
			if (strpos($k, 'db_') === 0) {
				return true;
			}
		}

		return false;
	}
}
