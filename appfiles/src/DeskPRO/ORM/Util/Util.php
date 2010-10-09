<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\ORM\Util;

use \Doctrine\ORM\PersistentCollection;

/**
 * Simple utility methods for working with the ORM
 */
class Util
{
	private function __construct() { /* Static class, no instances */ }


	
	/**
	 * Checks to see if $collection is a valid PersistentCollection, and if it's
	 * been initialized yet.
	 *
	 * @param mixed $collection
	 * @return bool
	 */
	public static function isCollectionInitialized($collection)
	{
		if ($collection instanceof PersistentCollection AND $collection->isInitialized()) {
			return true;
		}

		return false;
	}
}