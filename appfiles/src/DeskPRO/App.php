<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO;

use \Symfony\Component\DependencyInjection\ContainerInterface;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A global singleton that facilitates fetching well known objects and values.
 *
 * @static
 */
class App
{
	const DEFAULT_NAME = 'default';

	/**#@+
	 * Names of common services
	 */
	const SERVICE_DB              = 'database_connection';
	const SERVICE_ORM             = 'doctrine.orm.entity_manager';
	const SERVICE_INPUT_READER    = 'deskpro.core.input_reader';
	const SERVICE_INPUT_CLEANER   = 'deskpro.core.input_cleaner';
	const SERVICE_SETTINGS        = 'deskpro.core.settings';
	const SERVICE_SESSION         = 'session';
	/**#@-*/

	/**
	 * An array of registered containers
	 * @var array
	 */
	protected static $_containers = array();

	/**
	 * An array of loaded config files.
	 * @var array
	 */
	protected static $_fileconfig = array();

	/**
	 * Currently booted environment
	 * @var string
	 */
	protected static $_environment = null;

	/**
	 * Is debug mode enabled?
	 * @var bool
	 */
	protected static $_debug = false;

	/**
	 * The Kernel
	 * @var DeskPRO\Kernel\Kernel
	 */
	protected static $_kernel = null;

	

	/**
	 * Set the kernel
	 *
	 * @param \DeskPRO\Kernel\Kernel $kernel
	 */
	public static function setKernel(\DeskPRO\Kernel\Kernel $kernel)
	{
		if (self::$_kernel !== null) {
			throw new \BadMethodCallException('The kernel has already been set');
		}

		self::$_kernel = $kernel;
		self::$_environment = $kernel->getEnvironment();
		self::$_debug = $kernel->isDebug();
	}


	
	/**
	 * Set a container we'll use in the App to fetch various services
	 *
	 * @param ContainerInterface $container The container
	 * @param string             $name      A name for the container to reference it (such as 'default')
	 */
	public static function setContainer(ContainerInterface $container, $name = self::DEFAULT_NAME)
	{
		if (isset(self::$_containers[$name])) {
			throw new \InvalidArgumentException("The container with `$name` has already been set");
		}

		self::$_containers[$name] = $container;
	}
	


	/**
	 * Get a registered container.
	 *
	 * @param string $name
	 * @return ContainerInterface
	 */
	public static function getContainer($name = self::DEFAULT_NAME)
	{
		if (!isset(self::$_containers[$name])) {
			throw new \OutOfBoundsException("There is no container set with name `$name`");
		}
	}



	/**
	 * Get a service from some container.
	 *
	 * Supply null as $container_name and we'll go through all registered containers
	 * and return the first found.
	 *
	 * @param string $service_name    The service to get
	 * @param string $container_name  The container to get it from.
	 */
	public static function get($service_name, $container_name = self::DEFAULT_NAME)
	{
		if ($container_name !== null) {
			$container = self::getContainer($container_name);
			return $container->get($service_name);
		}

		foreach (self::$_containers as $container) {
			if ($container->has($service_name)) {
				return $container->get($service_name);
			}
		}

		throw new \OutOfBoundsException("There is no container with the service `$service_name`");
	}

	

	/**
	 * Get the DB abstraction object.
	 *
	 * @return DeskPRO\DBAL\Connection
	 */
	public static function getDb()
	{
		return self::get(self::SERVICE_DB, self::DEFAULT_NAME);
	}


	
	/**
	 * Get the ORM entity manager.
	 *
	 * @return DeskPRO\ORM\EntityManager
	 */
	public static function getOrm()
	{
		return self::get(self::SERVICE_ORM);
	}

	

	/**
	 * Get a cache object, or null if no cache exists.
	 * 
	 * @param string $name Name of the cache
	 * @return Zend\Cache\Frontend\Core
	 */
	public static function getCache($name)
	{
		$container = self::getContainer(self::DEFAULT_NAME);

		$service_name = 'deskpro.cache.' . $name;

		if ($container->has($service_name)) {
			return $container->get($service_name);
		}

		return null;
	}


	
	/**
	 * Get the kernel
	 *
	 * @return DeskPRO\Kernel\Kernel
	 */
	public static function getKernel()
	{
		if (!self::$_kernel) {
			throw new \RuntimeException('No kernel has been set yet');
		}

		return self::$_kernel;
	}



	/**
	 * Get the value of a setting.
	 *
	 * @param string $name The name of the setting to get
	 * @return string
	 */
	public static function getSetting($name)
	{
		$settings = self::get(self::SERVICE_SETTINGS);
		return $settings->get($name);
	}



	/**
	 * Get the filesystem directory where we want to store cache files.
	 *
	 * @return string
	 */
	public static function getCacheDir()
	{
		static $cache_dir = null;

		if ($cache_dir === null) {
			$dir = self::getConfig('cache_dir');
			if (!$dir) {
				$dir = DP_ROOT . '/sys/cache/%env%';
			}
			
			$dir = str_replace('%env%', self::$_environment, $dir);
			$cache_dir = $dir;
		}

		return $cache_dir;
	}



	/**
	 * Get the filesystem directory where log files are stored.
	 * 
	 * @return string
	 */
	public static function getLogDir()
	{
		$dir = self::getConfig('logs_dir');
		if (!$dir) {
			$dir = DP_ROOT . '/sys/logs';
		}

		return $dir;
	}

	

	/**
	 * Get the current env
	 *
	 * @return string
	 */
	public static function getEnvironment()
	{
		return self::$_environment;
	}


	
	/**
	 * Is debug mode enabled?
	 *
	 * @return bool
	 */
	public static function isDebug()
	{
		return self::$_debug;
	}



	/**
	 * Loads userconfig from the filesystem
	 * @param string $name The name of the user config
	 */
	protected static function _loadConfig($name = null)
	{
		if (!$name OR $name != self::DEFAULT_NAME) {
			$name = preg_replace('#[^a-zA-Z0-9\-_]#', '', $name);
			$filename = 'config.' . $name . '.php';
		} else {
			$name = self::DEFAULT_NAME;
			$filename = 'config.php';
		}
		
		$filepath = DP_ROOT . "/$filename";

		if (!file_exists($filepath)) {
			throw new \RuntimeException("$filename does not exist");
		}

		require($filepath);
		if (!isset($CONFIG)) {
			throw new \UnexpectedValueException("$filename does not define \$CONFIG");
		}

		self::$_userconfig[$name] = $CONFIG;
	}



	/**
	 * Get a config value from config.
	 *
	 * If $config_name is null, then entire config array from the file will be returned.
	 * $config_name can use dot notation to denote deep array keys.
	 *
	 * @param string $config_name  The config value to get
	 * @param mixed  $default      The value to return if no such key exists
	 * @param string $file_name    The file to fetch it form
	 */
	public static function getConfig($config_name, $default = null, $file_name = self::DEFAULT_NAME)
	{
		if (!isset(self::$_fileconfig[$file_name])) {
			self::_loadConfig($file_name);
		}

		$value = Arrays::getValue(self::$_fileconfig[$file_name], $config_name);
		if ($value === null) $value = $default;

		return $value;
	}
}