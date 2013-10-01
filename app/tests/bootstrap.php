<?php
define('DP_BOOT_MODE', 'testing');
require(realpath(__DIR__. '/../../index.php'));
require(__DIR__ . '/DatabaseTestCase.php');

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
		self::$kernel->shutdown();
		self::$kernel = null;
		self::$container = null;
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
}