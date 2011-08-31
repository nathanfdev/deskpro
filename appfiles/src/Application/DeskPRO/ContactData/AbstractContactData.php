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
use Application\DeskPRO\Entity\ContactDataAbstract;

use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Util;

abstract class AbstractContactData
{
	/**
	 * Apply form data to a contact record
	 *
	 * @param array $input
	 * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
	 */
	abstract public function applyFormData(array $input, ContactDataAbstract $contact_record);

	/**
	 * Return an array of values that are useful in a template
	 *
	 * @return array
	 */
	abstract public function getTemplateVars(ContactDataAbstract $contact_record);

	/**
	 * Get the short typename for this type
	 *
	 * @return string
	 */
	public static function getContactType()
	{
		$name = get_called_class();
		return Strings::camelCaseToUnderscore(Util::getBaseClassname($name));
	}
}
