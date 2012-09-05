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
*/

namespace DeskPRO\Kernel;

require_once DP_ROOT.'/sys/DpShutdown.php';
require_once DP_ROOT.'/sys/Kernel/HelpdeskOfflineMessage.php';

class KernelBooter
{
	/**
	 * Builds the main $DP_CONFIG array from config.php
	 *
	 * @return mixed
	 */
	public static function bootstrapConfig()
	{
		static $has_loaded = false;
		if ($has_loaded) {
			return;
		}

		$has_loaded = true;

		#------------------------------
		# Load main config now
		#------------------------------

		global $DP_CONFIG;
		dp_load_config();

		if (isset($DP_CONFIG['debug']['enable_debug_trace']) && $DP_CONFIG['debug']['enable_debug_trace']) {
			if (!function_exists('xdebug_start_trace')) {
				exit('To use the `debug.enable_debug_trace` setting, the xdebug extension must be installed');
			}

			$debug_dir = dp_get_debug_dir();

			if (!is_dir($debug_dir) || !is_writable($debug_dir)) {
				exit('The debug output directory at ' . $debug_dir . ' does not exist or is not writable.');
			}

			$file = $debug_dir . DIRECTORY_SEPARATOR . date('YmdHis') . '-' . mt_rand(10000,99999);
			xdebug_start_trace($file);
			ini_set('xdebug.collect_params', 3);
			define('DP_DEBUG_TRACE_FILE', $file . '.xt');
		}
	}


	/**
	 * Includes the libraries and autoloading required for the system to boot
	 *
	 * @param $debug
	 * @return mixed
	 */
	public static function bootstrapLib($debug)
	{
		static $has_loaded = false;
		if ($has_loaded) {
			return;
		}

		$has_loaded = true;

		if ($debug || defined('DP_BUILDING') || !file_exists(DP_ROOT . '/sys/bootstrap.php') || !file_exists((DP_ROOT . '/sys/compiled.php'))) {
			require(DP_ROOT . '/sys/bootstrap-dev.php');
		} else {
			require(DP_ROOT . '/sys/bootstrap.php');
			require(DP_ROOT . '/sys/compiled.php');
		}

		if (isset($GLOBALS['DP_AUTOLOADER']) && !dp_get_config('no_use_classmap_file') && file_exists(DP_ROOT.'/sys/cache/classmap.php')) {
			$map = require DP_ROOT.'/sys/cache/classmap.php';
			if ($map) {
				$GLOBALS['DP_AUTOLOADER']->registerClassNames($map);
			}
		}

		require(DP_ROOT . '/sys/system.php');
	}


	/**
	 * Gets the environment ready for execution
	 */
	public static function bootstrapEnv()
	{
		#------------------------------
		# Normalize env
		#------------------------------

		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

		\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT.'/vendor/php-utf8');

		#------------------------------
		# Undo magic quotes
		#------------------------------

		// Check exists since its gone in PHP 5.4
		if (function_exists('get_magic_quotes_gpc')) {
			ini_set('magic_quotes_runtime', 0);

			if (get_magic_quotes_gpc()) {
				$clean_fn = function(&$v) {
					$v = stripslashes($v);
				};

				array_walk_recursive($_GET,     $clean_fn);
				array_walk_recursive($_POST,    $clean_fn);
				array_walk_recursive($_COOKIE,  $clean_fn);
				array_walk_recursive($_REQUEST, $clean_fn);
			}
		}
	}


	/**
	 * Boots a web kernel
	 *
	 * @param null $request
	 */
	public static function bootWeb($request = null)
	{
		global $DP_CONFIG;

		self::bootstrapConfig();

		$env = 'prod';
		$debug = false;

		if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
			$env = 'dev';
			$debug = true;
		}

		self::ensureEnvFiles($env);
		self::bootstrapLib($debug);
		self::bootstrapEnv();

		if (!$request) {
			$request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();
		}
		$path = $request->getPathInfo();

		if (preg_match('#^/agent(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'agent');
		} elseif (preg_match('#^/admin(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AdminKernel';
			define('DP_INTERFACE', 'admin');
		} elseif (preg_match('#^/billing(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\BillingKernel';
			define('DP_INTERFACE', 'billing');
		} elseif (preg_match('#^/reports(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\ReportKernel';
			define('DP_INTERFACE', 'reports');
		} elseif (preg_match('#^/api(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\ApiKernel';
			define('DP_INTERFACE', 'api');
		} elseif (preg_match('#^/dev(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'dev');
		} elseif (preg_match('#^/install(/|\?|$)#', $path)) {

			if (dp_get_config('is_installed_flag')) {
				echo deskpro_install_basic_error("DeskPRO has already been installed. If this is a mistake, remove the data/is_installed.dat file to make the installer function again.", 'Error');
				exit;
			}

			$kernel_class = 'DeskPRO\\Kernel\\InstallKernel';
			define('DP_INTERFACE', 'install');

			// Always force full URL with trailing slash
			if (strpos($request->getRequestUri(), '/index.php/install/') === false) {
				header('Location: ' . $request->getBasePath() . '/index.php/install/');
				exit;
			}

		} elseif (preg_match('#^/tech(/|\?|$)#i', $path)) {
			header('Location: ' . $request->getBasePath() . '/agent');
			exit;
		} elseif (preg_match('#^/admincp(/|\?|$)#i', $path)) {
			header('Location: ' . $request->getBasePath() . '/admin');
			exit;
		} else {
			$kernel_class = 'DeskPRO\\Kernel\\UserKernel';
			define('DP_INTERFACE', 'user');
		}

		// No access to install or dev from cloud
		if (defined('DPC_IS_CLOUD') && (DP_INTERFACE == 'dev' || DP_INTERFACE == 'install')) {
			exit;
		}

		#------------------------------
		# Handle request
		#------------------------------

		define('DP_REQUEST_URL', $request->getUri());

		try {
			$kernel = new $kernel_class($env, $debug);
			$kernel->handle($request)->send();
		} catch (\PDOException $e) {
			if ($e->getCode() == '2002' || $e->getCode() == '1049' || $e->getCode() == '1044' || $e->getCode() == '1045') {
				// This will show an error page if already installed, so the redirect to install wont happen
				deskpro_handle_boot_db_exception($e);

				header('Location: ' . $request->getBasePath() . '/index.php/install/');
				exit;
			}
			throw $e;
		}
	}


	/**
	 * Boots the CLI
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootCli($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp('cmd', $env, $debug);

		if (!$app) {
			return;
		}

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$app->setAutoExit(false);
		$return = $app->run();
		unset($GLOBALS['DP_IS_IN_CLI']);

		return $return;
	}


	/**
	 * Boots the CLI and runs the cron command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootCron($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp('cron', $env, $debug, true);

		if (!$app) {
			return;
		}

		$do_upgrade = false;
		try {
			if (\Application\DeskPRO\App::getSetting('core.upgrade_time') && \Application\DeskPRO\App::getSetting('core.upgrade_time') <= time()) {
				$do_upgrade = true;
			}
		} catch (\Exception $e) {throw $e;}

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php

		if ($do_upgrade) {
			$argv = array();
			array_unshift($argv, 'cron.php', 'dp:internal-upgrade-runner');
		} else {
			array_unshift($argv, 'cron.php', 'dp:worker-job'); // so we can add the command name in the right spot
		}

		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$app->setAutoExit(false);
		$return = $app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;

		return $return;
	}


	/**
	 * Boots the CLI runs the import CLI command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootImport($env = 'prod', $debug = false)
	{
		static::ensureCli();

		$app = static::getCliApp('import', $env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php
		array_unshift($argv, 'import.php', 'dp:import', '--run'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$GLOBALS['DP_IS_INSTALL'] = true;
		$app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;
		$GLOBALS['DP_IS_INSTALL'] = false;
	}


	/**
	 * Boots the CLI runs the upgrade CLI command
	 *
	 * @param string $env
	 * @param bool $debug
	 */
	public static function bootUpgrade($env = 'prod', $debug = false)
	{
		static::ensureCli();

		$app = static::getCliApp('upgrade', $env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove upgrade.php
		array_unshift($argv, 'upgrade.php', 'dp:upgrade'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$GLOBALS['DP_IS_IN_CLI'] = true;
		$GLOBALS['DP_IS_INSTALL'] = true;
		$app->run($input);
		$GLOBALS['DP_IS_IN_CLI'] = false;
		$GLOBALS['DP_IS_INSTALL'] = false;
	}


	/**
	 * Creates a CLI kernel, and create an console app
	 *
	 * @param string $env
	 * @param bool $debug
	 * @return \Symfony\Bundle\FrameworkBundle\Console\Application
	 */
	public static function getCliApp($mode, $env = 'prod', $debug = false, $enforce_offline_mode = false)
	{
		global $DP_CONFIG;

		self::bootstrapConfig();

		if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
			$env = 'dev';
			$debug = true;
		}

		self::ensureEnvFiles($env);
		self::bootstrapLib($debug);
		self::bootstrapEnv();

		if (defined('DP_BUILDING')) {
			$debug = false;
		}

		define('DP_INTERFACE', 'cli');
		$kernel = new \DeskPRO\Kernel\CliKernel($env, $debug);
		$kernel->boot($mode);

		try {
			if ($mode == 'cron' && $kernel->isUpgradePending()) {
				if (in_array('--verbose', $_SERVER['argv'])) {
					echo "Upgrade pending\n";
				}
				return null;
			}
			if ($enforce_offline_mode || ($mode == 'cron' && !App::getSetting('core.setup_initial'))) {
				if ($kernel->isHelpdeskOffline()) {
					if (in_array('--verbose', $_SERVER['argv'])) {
						echo "Helpdesk offline\n";
					}
					return null;
				}
			}
		} catch (\PDOException $e) {
			global $DP_CONFIG;
			if ($e->getCode() == '42S02' || @$DP_CONFIG['db']['user'] == 'YOUR_DATABASE_USER' || @$DP_CONFIG['db']['password'] == 'YOUR_DATABASE_PASS' || @$DP_CONFIG['db']['dbname'] == 'YOUR_DATABASE_NAME') {
				echo "DeskPRO is not yet installed. If you believe this a mistake, check your config.php\n";
				echo "file and ensure the database connection details are correct.\n";
				echo "\n";
				echo "The connection attempt resulted in the following error:\n[{$e->getCode()}] {$e->getMessage()}";
				echo "\n";
				exit;
			}

			// Otherwise unknown error we'll throw up
			throw $e;
		}

		$app = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
		$app->setCatchExceptions(false);
		return $app;
	}


	/**
	 * Ensures the current invocation is via the command-line
	 */
	public static function ensureCli()
	{
		if (php_sapi_name() != 'cli') {
			echo "This script must only be run from the CLI.\n";
			echo "Contact support@deskpro.com if you require assistance.\n";
			exit(1);
		}
	}


	/**
	 * If in prod mode, ensures that the build files etc exist.
	 * If not in prod mode, ensures that the cached ir exists and is writable.
	 */
	public static function ensureEnvFiles($env)
	{
		global $DP_CONFIG;
		$cache_dir = DP_ROOT.'/sys/cache';
		$web_dir = realpath(DP_ROOT . '/../web');

		$offline = false;
		if (is_file(dp_get_data_dir() . '/helpdesk-offline.trigger')) {
			$offline = true;
		}

		#------------------------------
		# Prod mode: make sure built
		#------------------------------

		if ($env == 'prod' && (!is_dir($cache_dir . '/prod'))) {
			if (php_sapi_name() == 'cli') {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflineMessage();
					exit(1);
				}
				echo <<<'TXT'
DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:

	1) Enable dev mode by editing /config.php and adding these lines:

		$DP_CONFIG['debug'] = array();
		$DP_CONFIG['debug']['dev'] = true;
		$DP_CONFIG['debug']['raw_assets'] = array('all');

	2) Or alternatively you can build DeskPRO by running app/bin/build/build.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<'HTML'
<p>DeskPRO's internal build files are missing. If you are using a pristine copy of the source code, you will need to do one of the following:<br /><br /></p>

<p>1) Enable dev mode by editing <code>/config.php</code> and adding these lines:

<pre>
$DP_CONFIG['debug'] = array();
$DP_CONFIG['debug']['dev'] = true;
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build DeskPRO by running <code>app/bin/build/build.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO');
			}
			exit;

		#------------------------------
		# Dev mode, make sure cache dir writable
		#------------------------------

		} elseif ($env == 'dev' && (!is_dir($cache_dir) || !is_writable($cache_dir))) {
			if ($offline) {
				echo HelpdeskOfflineMessage::getOfflineMessage();
				exit(1);
			}
			if (php_sapi_name() == 'cli') {
				echo <<<TXT
DeskPRO is currently in dev mode which requires the cache directory at $cache_dir to be writable. Please
ensure this directory is writable and try again.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<HTML
<p>DeskPRO is currently in dev mode which requires the cache directory at <code>$cache_dir</code> to be writable. Please
make this directory writable and try again.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
			}

			exit;

		#------------------------------
		# Dev mode, not using raw assets, no build files
		#------------------------------

		} elseif ($env == 'dev' && (empty($DP_CONFIG['debug']['raw_assets']) && !is_file($web_dir.'/build/js/agent-all.js'))) {
			if (php_sapi_name() == 'cli') {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflineMessage();
					exit(1);
				}
				echo <<<'TXT'
You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:

	1) Enable raw assets by editing /config.php and adding this line:

		$DP_CONFIG['debug']['raw_assets'] = array('all');

	2) Or alternatively you can build assets by running app/bin/build/build-assetic.php from the command-line.

Email support@deskpro.com if you need assistance or got this message unexpectedly.

TXT;

				exit(1);
			} else {
				if ($offline) {
					echo HelpdeskOfflineMessage::getOfflinePage();
					exit(1);
				}
				$html = <<<'HTML'
<p>You are running in dev mode but you have not enabled raw assets and assets have not been built yet. For pages to display properly, you will need to do one of the following:<br /><br /></p>

<p>1) Enable raw assets by editing <code>/config.php</code> and adding this line:

<pre>
$DP_CONFIG['debug']['raw_assets'] = array('all');
</pre>
</p>

<p>2) Or alternatively you can build assets by running <code>app/bin/build/build-assetic.php</code> from the command-line.<br /><br /></p>

<p>Email support@deskpro.com if you need assistance or got this message unexpectedly.</p>
HTML;

				echo deskpro_install_basic_error($html, 'DeskPRO Dev Mode');
			}
			exit;
		}
	}


	/**#@+
	 * Handling of shutdown stack and xdebug traces
	 */
	private static function DeskPRO_Done_MarkerCheck() {}
	public static function DeskPRO_Done()
	{
		static $called = false;
		if ($called) return;
		$called = true;

		\DpShutdown::run();

		if (!defined('DP_DEBUG_TRACE_FILE')) {
			return;
		}

		self::DeskPRO_Done_MarkerCheck();
		xdebug_stop_trace();

		if (isset($GLOBALS['DP_CONFIG']['debug']['enable_debug_trace_keep']) AND $GLOBALS['DP_CONFIG']['debug']['enable_debug_trace_keep']) {
			return;
		}

		$fp = @fopen(DP_DEBUG_TRACE_FILE, 'r');
		if ($fp) {
			@fseek($fp, -150000, \SEEK_END);
			$chunk = @fread($fp, 150000);
			@fclose($fp);

			if (strpos($chunk, 'DeskPRO_Done_MarkerCheck') !== false) {
				@unlink(DP_DEBUG_TRACE_FILE);
			}
		}
	}
	/**#@-*/
}
