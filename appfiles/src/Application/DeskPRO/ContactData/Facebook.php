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

class Twitter extends AbstractContactData
{
	/**
	 * Apply form data to a contact record
	 *
	 * @param array $input
	 * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
	 */
	public function applyFormData(array $input, ContactDataAbstract $contact_record)
	{
		$contact_record->comment = $input['comment'];
		$contact_record->field_1 = $input['profile_url'];

		if (preg_match('#/profile\.php?id=([0-9]+)#', $input['profile_url'], $m)) {
			$contact_record->field_2 = $m[1];
		} elseif (preg_match('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $input['profile_url'], $m)) {
			$contact_record->field_2 = $m[1];
		} elseif (preg_match('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $input['profile_url'], $m)) {
			$contact_record->field_2 = $m[1];
		} else {
			$contact_record->field_2 = Strings::extractRegexMatch('#(facebook\.com.*?)$#', $input['profile_url'], $m);
		}
	}

	/**
	 * Return an array of values that are useful in a template
	 *
	 * @return array
	 */
	public function getTemplateVars(ContactDataAbstract $contact_record)
	{
		return array(
			'comment'     => $contact_record->comment,
			'profile_url' => $contact_record->field_1,
			'display'     => $contact_record->field_2
		);
	}
}
