<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * Settings used by the system.
 *
 * @Entity
 */
class UsergroupPropertyPermission extends \Application\CoreBundle\Entity\UsergroupProperty
{
	const PROPERTY_TYPE = 'permission';

	/**
	 * Coalesces an array of permissions and combines them into one collection of
	 * effective permissions. In other words, when a user belongs to multiple usergroups,
	 * this will take all those properties and combine them so we know the users
	 * end-result permissions.
	 *
	 * @param array $properties An array of UsergroupPropertyPermission
	 * @return array
	 */
	public static function coalescePermissionProperties(array $properties)
	{
		$effective = array();

		foreach ($properties as $prop) {
			if ($prop['flag'] !== null) {
				$type = 1;
			} elseif ($prop['data'] !== null) {
				$type = 2;
			} else {
				continue; // no value, so this is a useless property :)
			}

			switch ($type) {
				// flag type
				case 1:
					// - With flags, we take yes (true) values only if there is no
					// existing value retrieved. No (false) values always override.
					// - In other words, no values are destructive.
					// - So you set yes to give perms, null value to not override, and no to override a yes

					if (!$prop['flag']) {
						$effective[$prop['name']] = false;
					} elseif (!isset($effective[$prop['name']])) {
						$effective[$prop['name']] = true;
					}

					break;

				// data type
				case 2:

					// - Data type, we treat numeric values specially and always
					// take a higher value. So for things like upload limit would be a
					// numeric data value, and the user would get the highest value.
					// - Otherwise, we cant make an informed choice and simply return the
					// first. But the permissions props are only meant for numeric values so
					// that should never happen.

					if (is_numeric($prop['data'])) {
						if (!isset($effective[$prop['name']]) OR $effective[$prop['name']] < $prop['data']) {
							$effective[$prop['name']] = $prop['data'];
						}
					} elseif (!isset($effective[$prop['name']])) {
						$effective[$prop['name']] = $prop['data'];
					}

					break;
			}

			return $effective;
		}
	}
}