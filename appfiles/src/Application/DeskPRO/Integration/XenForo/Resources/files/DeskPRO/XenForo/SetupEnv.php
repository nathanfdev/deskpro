<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage XenForo
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */



/**
 * Simple class that sets up the environment for various DeskPRO addons.
 * It's a PHP5 style class (not namespaced) because XF doesn't autoload
 * namespaced classes.
 */
class DeskPRO_Integration_XenForo_SetupEnv
{
	/**
	 * @var DeskPRO_XenForo_SetupEnv
	 */
	protected static $_instance = null;

	protected $_root_dir = null;

	/**
	 * @var Symfony\Framework\UniversalClassLoader
	 */
	protected $_autoloader = null;

	protected function __construct()
	{

		$config = XenForo_Application::get('config');
		if (isset($config->DeskPRO->rootDir)) {
			$root_dir = $config->DeskPRO->rootDir;
		} else {
			$root_dir = XenForo_Autoloader::getInstance()->getRootDir();
		}

		$this->_root_dir = $root_dir;

		require($this->_root_dir . '/vendor/symfony/src/Symfony/Framework/UniversalClassLoader.php');
		$this->_autoloader = new \Symfony\Framework\UniversalClassLoader();
		$this->_autoloader->register();
	}

	/**
	 * @return DeskPRO_XenForo_SetupEnv
	 */
	public static function getInstance()
	{
		if (self::$_instance == null) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}



	/**
	 * Called in init_dependencies to set up the DP environment. Every DeskPRO
	 * addon may use this hook, so it is only ever called once.
	 */
	public static function init()
	{
		if (self::$_instance !== null) return;

		$root_dir = XenForo_Autoloader::getInstance()->getRootDir();

		$inst = self::getInstance();
		$inst->getAutoloader()->registerNamespace('DeskPRO', $inst->getRootDir() . '/src');
		$inst->getAutoloader()->registerNamespace('Orb', $inst->getRootDir() . '/src');
		$inst->getAutoloader()->registerNamespace('Symfony', $inst->getRootDir() . '/vendor/symfony/src');

		$our_loader = new \Application\DeskPRO\Integration\XenForo\AutoloaderInjector(XenForo_Autoloader::getInstance(), $inst->getAutoloader());
		XenForo_Autoloader::setInstance($our_loader);
	}



	/**
	 * Get the autolaoder
	 *
	 * @return Symfony\Framework\UniversalClassLoader
	 */
	public function getAutoloader()
	{
		return $this->_autoloader;
	}



	/**
	 * Get the root dir to DeskPRO-related files.
	 *
	 * @return string
	 */
	public function getRootDir()
	{
		return $this->_root_dir;
	}
}