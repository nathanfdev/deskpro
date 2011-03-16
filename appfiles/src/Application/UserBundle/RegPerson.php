<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

class RegPerson
{
	public $person_values = array();
	public $person_custom_values = array();

	public function setPerson(Entity\Person $person)
	{
		if (!$person['id']) return;

		$this->person = $person;

		$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$this->person_custom_values = App::getApi('custom_fields.util')->createFormData($person['custom_data'], $field_defs);
	}

	/**
	 * Save the new ticket
	 *
	 * @return void
	 */
	public function save()
	{
		App::getOrm()->beginTransaction();

		$this->person->fromArray($this->person_values);
		if ($this->person_custom_values) {
			$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
			foreach ($field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($this->person_custom_values) as $info) {
					$this->person->setCustomData($info[0], $info[1], $info[2]);
				}
			}
		}

		$this->person['is_user'] = true;

		App::getOrm()->persist($this->person);
		App::getOrm()->flush();

		App::getOrm()->commit();
	}
}