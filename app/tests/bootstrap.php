<?php
define('DP_BOOT_MODE', 'testing');
require(realpath(__DIR__. '/../../index.php'));
require(__DIR__ . '/ContainerTestCase.php');
require(__DIR__ . '/DatabaseTestCase.php');

@file_put_contents(__DIR__.'/../../running_tests.trigger', time());
register_shutdown_function(function() {
	@unlink(__DIR__.'/../../running_tests.trigger');
});

class DpTestConfig
{
	/**
	 * @var \DeskPRO\Kernel\CliKernel
	 */
	private static $kernel;

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private static $container;


	/**
	 * Shut-down the kernel and unset container. Next call to getContainer will be a new one.
	 */
	public static function resetContainer()
	{
		if (self::$kernel) {
			self::$kernel->shutdown();
			self::$kernel = null;
		}
		if (self::$container) {
			self::$container = null;
		}
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public static function getContainer()
	{
		if (self::$container) {
			return self::$container;
		}

		$kernel = new \DeskPRO\Kernel\CliKernel('dev', true);
		$kernel->boot('cli');
		self::$container = $kernel->getContainer();
		\Application\DeskPRO\App::setContainer(self::$container, 'default', true);

		return self::$container;
	}


	/**
	 * @return \DeskPRO\Kernel\CliKernel
	 */
	public static function getKernel()
	{
		self::getContainer();
		return self::$kernel;
	}


	/**
	 * Inits behat tests by setting up a db set
	 */
	public static function initForBehat()
	{
		$set_name = dp_get_config('testing_db_set');
		if (!$set_name) {
			$set_name = 'FreshDb';
		}

		self::initTestDb($set_name, true);
	}


	/**
	 * Resets the test database
	 */
	public static function initTestDb($set_name = 'FreshDb', $force = false)
	{
		require_once(DP_ROOT . '/tests/DbSet/AbstractDbSet.php');
		if (file_exists(DP_ROOT . '/tests/DbSet/'.$set_name.'.php')) {
			require_once(DP_ROOT . '/tests/DbSet/'.$set_name.'.php');
		}

		$cache_path = DP_ROOT.'/tests/build/db-sets';
		if (!is_dir($cache_path)) {
			mkdir($cache_path);
			chmod($cache_path, 0777);
		}

		$set_class = "DeskPRO\\Tests\\DbSet\\$set_name";
		if (!class_exists($set_class)) {
			throw new \InvalidArgumentException("Invalid set name: $set_name ($set_class)");
		}

		$set = new $set_class(self::getContainer());
		$set->enableCache(
			$cache_path,
			'mysql',
			'mysqldump'
		);
		$set->install($force);
		self::resetContainer();
	}
}