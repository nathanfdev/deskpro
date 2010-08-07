<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Util
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO;

/**
 * This is an app registry and utility class used throughout.
 * It is a registry but also serves as some generic low-level functionality like loading data files.
 *
 * @static
 */
class App extends \Zend\Registry
{
	/**
	 * The main service container.
	 *
	 * @static
	 * @return ContainerInterface
	 */
	public static function getContainer()
	{
		return self::get('container');
	}


	/**
	 * Gets the main core cache object.
	 *
	 * @static
	 * @return Zend_Cache
	 */
	public static function getCache()
	{
		$container = self::getContainer();
		return $container->getDeskpro_Core_Cache();
	}
}