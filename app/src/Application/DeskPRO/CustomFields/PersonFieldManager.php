<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @subpackage
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Orb\Auth\Identity;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class PersonFieldManager extends FieldManager
{
	/**
	 * Get an array of all defined fields (by doing a query).
	 *
	 * @return array
	 */

	public function getDefinedFields()
	{
		return array_values($this->em->getRepository('DeskPRO:CustomDefPerson')->getTopFields());
	}

	/**
	 * @param string $id
	 * @param bool   $enabled
	 */

	public function setFieldEnabledById($id, $enabled = true)
	{
		if ($custom_field_id = Strings::extractRegexMatch('#^field_(\d+)$#', $id)) {

			$field             = $this->em->find('DeskPRO:CustomDefPerson', $custom_field_id);
			$field->is_enabled = $enabled;

			$this->em->persist($field);
			$this->em->flush($field);
		}
	}

	public function copyUsersourceData(Person $person, Identity $identity, Usersource $usersource)
	{
		$save_data = array();

		foreach ($this->getDefinedFields() as $field) {
			if ($field->handler_class != 'Application\\DeskPRO\\CustomFields\\Handler\\Data') {
				continue;
			}

			if ($field->getOption('usersource_id') && $field->getOption('usersource_id') != $usersource->getId()) {
				continue;
			}

			$field_name = $field->getOption('field_name');
			$raw_data   = $identity->getRawData();

			// Reads the value and does some common input error correction:
			// - Arrays are separated by a slash or a dot: telephonenumber.0 or telephonenumber/0
			// - If the key isnt found as-is, we'll also try converting to lowercase and trying again (keys are typically lowercase)
			foreach (array('/', '.') as $sep) {
				$val = Arrays::keyAsPath($raw_data, $field_name, $sep, null);

				if ($val === null) {
					$val = Arrays::keyAsPath($raw_data, strtolower($field_name), $sep, null);
				}

				if ($val !== null) {
					break;
				}
			}

			if ($val === null) {
				continue;
			}

			// Automatically flatten arrays
			// E.g., LDAP will return attributes as an array, often with only one item
			// Without this we'd need to document that you need to specify attrName/0
			if (is_array($val)) {
				$val = implode("\n\n", $val);
			}

			$save_data['field_' . $field->getId()] = $val;
		}

		if ($save_data) {
			$this->saveFormToObject($save_data, $person, true);
		}
	}
}
