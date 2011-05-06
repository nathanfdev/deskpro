<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Translate;

use \Application\DeskPRO\App;

use \Orb\Util\Util;

/**
 * This takes an object, and then based on its state, produces a phrase ID that
 * we can use to look up a phrase. This is how we translate thigns like category
 * titles. The category itself becomes a "phrase", and is handled like any other.
 */
class ObjectPhraseNamer
{
	public function getPhraseName($object, $property = null)
	{
		if ($object instanceof \ArrayAccess AND isset($object['id'])) {
			$baseclass = Util::getBaseClassname($object);
			$prefix = 'obj_' . strtolower($baseclass) . '.';
			$name = $prefix . $object['id'];
			if ($property) {
				$name .= '_' . $property;
			}
			return $name;
		}

		return null;
	}

	public function getPhraseDefault($object, $property = null)
	{
		if ($object instanceof \ArrayAccess) {
			if ($property === null) {
				if (isset($object['full_title'])) {
					return $object['full_title'];
				} elseif (isset($object['title'])) {
					return $object['title'];
				} elseif (isset($object['name'])) {
					return $object['title'];
				}
			}

			if (isset($object[$property])) {
				return $object[$property];
			}
		}

		return null;
	}
}