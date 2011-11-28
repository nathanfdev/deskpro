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

class Address extends AbstractContactData
{
	/**
	 * Apply form data to a contact record
	 *
	 * @param array $input
	 * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
	 */
	public function applyFormData(array $input, ContactDataAbstract $contact_record)
	{
		$contact_record->comment = isset($input['comment']) ? $input['comment'] : '';
		$contact_record->field_1 = isset($input['address']) ? $input['address'] : '';
		$contact_record->field_2 = isset($input['city']) ? $input['city'] : '';
		$contact_record->field_3 = isset($input['state']) ? $input['state'] : '';
		$contact_record->field_4 = isset($input['zip']) ? $input['zip'] : '';
		$contact_record->field_5 = isset($input['country']) ? $input['country'] : '';
	}

	/**
	 * Return an array of values that are useful in a template
	 *
	 * @return array
	 */
	public function getTemplateVars(ContactDataAbstract $contact_record)
	{
		$params = array(
			'sensor' => 'false',
			'size' => '200x200',
			'center' => str_replace("\n", " ", Strings::standardEol(
				$contact_record->field_1 . ' '
				. $contact_record->field_2
				. $contact_record->field_3
				. $contact_record->field_4
				. $contact_record->field_5
			))
		);
		$google_url = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query($params, null, '&amp;');

		return array(
			'comment' => $contact_record->comment,
			'address' => $contact_record->field_1,
			'city' => $contact_record->field_2,
			'state' => $contact_record->field_3,
			'zip' => $contact_record->field_4,
			'country' => $contact_record->field_5,
			'address_html' => nl2br(htmlentities($contact_record->field_1)),
			'map_url' => $google_url,
		);
	}
}
