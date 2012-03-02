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
