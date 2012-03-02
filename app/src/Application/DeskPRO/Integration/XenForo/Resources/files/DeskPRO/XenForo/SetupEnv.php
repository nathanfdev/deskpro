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
 * @subpackage XenForo
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
