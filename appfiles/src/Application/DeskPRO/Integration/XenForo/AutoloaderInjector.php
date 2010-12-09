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

namespace Application\DeskPRO\Integration\XenForo;



/**
 * Customized autoloader that uses a Symfony autoloader if the XF one fails
 */
class AutoloaderInjector extends \XenForo_Autoloader
{
	/**
	 * The real autoloader created by XF
	 * @var XenForo_Autoloader
	 */
	protected $_xf_autoloader;

	/**
	 * @var Symfony\Framework\UniversalClassLoader
	 */
	protected $_sf_autoloader;

	public function __construct(\XenForo_Autoloader $xf_autoloader, \Symfony\Framework\UniversalClassLoader $sf_autoloader)
	{
		$this->_xf_autoloader = $xf_autoloader;
		$this->_sf_autoloader = $sf_autoloader;
	}

	public function autoload($class)
	{
		if (!$this->_xf_autoloader->autoload($class)) {
			$this->_sf_autoloader->loadClass($class);
		}

		if (class_exists($class, false) || interface_exists($class, false)) {
			return true;
		}

		return false;
	}

	public function setupAutoloader($rootDir) { }

	public function getRootDir()
	{
		return $this->_xf_autoloader->getRootDir();
	}
}