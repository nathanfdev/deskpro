<?php

namespace DeskPRO\Kernel;

class KernelBooter
{
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
		if (is_array($DP_CONFIG)) {
			return;
		}

		require DP_CONFIG_FILE;

		if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
			$DP_CONFIG = array();
		}

		if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
		if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = DP_DATABASE_HOST;
		if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = DP_DATABASE_USER;
		if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = DP_DATABASE_PASSWORD;
		if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = DP_DATABASE_NAME;

		if (!defined('DP_BUILD_TIME')) {
			if (file_exists(DP_ROOT.'/sys/config/build-time.php')) {
				require(DP_ROOT.'/sys/config/build-time.php');
			} else {
				define('DP_BUILD_TIME', 1323444089); // would be used by someone who hasnt built yet
			}
		}
	}

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

		require(DP_ROOT . '/sys/system.php');
	}

	public static function bootEnv()
	{

	}

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

		self::bootstrapLib($debug);

		if (!$request) {
			$request = \Application\DeskPRO\HttpFoundation\Request::createfromGlobals();
		}
		$path = $request->getPathInfo();

		// Always force index.php
		if (strpos($request->getRequestUri(), '/index.php') === false) {
			header('Location: ' . rtrim($request->getBasePath(), '/') . '/index.php' . $path);
			exit;
		}

		if (preg_match('#^/agent(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'agent');
		} elseif (preg_match('#^/admin(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AdminKernel';
			define('DP_INTERFACE', 'admin');
		} elseif (preg_match('#^/reports(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\ReportKernel';
			define('DP_INTERFACE', 'reports');
		} elseif (preg_match('#^/api(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'api');
		} elseif (preg_match('#^/dev(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\AgentKernel';
			define('DP_INTERFACE', 'dev');
		} elseif (preg_match('#^/dp(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\SysKernel';
			define('DP_INTERFACE', 'sys');
		} elseif (preg_match('#^/install(/|\?|$)#', $path)) {
			$kernel_class = 'DeskPRO\\Kernel\\InstallKernel';
			define('DP_INTERFACE', 'install');

			// Always force full URL with trailing slash
			if (strpos($request->getRequestUri(), '/index.php/install') === false) {
				header('Location: ' . $request->getServerBaseUrl() . '/index.php/install/');
				exit;
			}

		} elseif (preg_match('#^/tech(/|\?|$)#i', $path)) {
			header('Location: ' . $request->getServerBaseUrl() . '/agent');
			exit;
		} elseif (preg_match('#^/admincp(/|\?|$)#i', $path)) {
			header('Location: ' . $request->getServerBaseUrl() . '/admin');
			exit;
		} else {
			$kernel_class = 'DeskPRO\\Kernel\\UserKernel';
			define('DP_INTERFACE', 'user');
		}

		#------------------------------
		# Handle request
		#------------------------------

		try {
			$kernel = new $kernel_class($env, $debug);
			$kernel->handle($request)->send();
		} catch (\PDOException $e) {
			if ($e->getCode() == '1049') {

				// Attempt to create an empty database
				try {
					$dbh = new \PDO("mysql:host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
					$dbh->exec("CREATE DATABASE `{$DP_CONFIG['db']['dbname']}`");
					$success = true;
				} catch (\Exception $e) {
					$success = false;
				}

				if ($success) {
					header('Location: ' . $request->getServerBaseUrl() . '/index.php/install/');
					exit;
				}

				echo deskpro_install_basic_error('<ul><li>The database name you have set in <code>/config.php</code> does not exist</li></ul>');
			} elseif ($e->getCode() == '1044' || $e->getCode() == '1045') {
				echo deskpro_install_basic_error('<ul><li>The database user you have set in <code>/config.php</code> does not exist or does not have permission to use the database</li></ul>');
			} else {
				throw $e;
			}
		}
	}

	public static function bootCli($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp($env, $debug);
		$GLOBALS['DP_IS_IN_CLI'] = true;
		$app->run();
		unset($GLOBALS['DP_IS_IN_CLI']);
	}

	public static function bootCron($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp($env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php
		array_unshift($argv, 'cron.php', 'dp:worker-job'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$app->run($input);
	}

	public static function bootUpgrade($env = 'prod', $debug = false)
	{
		static::ensureCli();
		$app = static::getCliApp($env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php
		array_unshift($argv, 'upgrade.php', 'dp:import', '--run'); // so we can add the command name in the right spot
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$app->run($input);
	}

	protected static function getCliApp($env = 'prod', $debug = false)
	{
		global $DP_CONFIG;
		self::bootstrapConfig();

		if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
			$env = 'dev';
			$debug = true;
		}

		self::bootstrapLib($debug);

		if (defined('DP_BUILDING')) {
			$debug = false;
		}

		$kernel = new \DeskPRO\Kernel\CliKernel($env, $debug);

		define('DP_INTERFACE', 'cli');

		$app = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
		$app->setCatchExceptions(false);
		return $app;
	}

	protected static function ensureCli()
	{
		if (php_sapi_name() != 'cli') {
			echo "This script must only be run from the CLI.\n";
			echo "Contact support@deskpro.com if you require assistance.\n";
			exit(1);
		}
	}
}
