<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class ContactData
{
	protected static $instances = array();

	public static function getHandler($typename)
	{
		if (isset(self::$instances[$typename])) {
			return self::$instances[$typename];
		}

		$classname = 'Application\\DeskPRO\\ContactData\\' . Strings::underscoreToCamelCase($typename);
		if (!class_exists($classname)) {
			throw new \InvalidArgumentException("`$typename` is not a valid type");
		}

		self::$instances[$typename] = new $classname();

		return self::$instances[$typename];
	}
}
