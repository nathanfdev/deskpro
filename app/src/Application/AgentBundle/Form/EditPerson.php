<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Form;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\FormField;
use Application\DeskPRO\Entity\PersonFieldData;
use Application\DeskPRO\Entity\Person;

class EditPerson extends \Orb\Form\Field\Form
{
	protected $person_fields;

	protected function init()
	{
		$basic_fields = new\Orb\Form\Field\FieldGroup(array('name' => 'basic_fields'));

		$f = new \Orb\Form\Field\Text(array('name' => 'first_name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Text(array('name' => 'last_name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Text(array('name' => 'name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Password(array('name' => 'password'));
		$basic_fields->addField($f);

		$this->addField($basic_fields);

		// del emails is just an array we'll process on save
		// The template needs to actually output the proper fields
		$f = new \Orb\Form\Field\FieldGroup(array('name' => 'del_emails'));
		$this->addField($f);

		// New email
		$f = new \Orb\Form\Field\Text(array('name' => 'new_email'));
		$this->addField($f);
	}

	public function setCustomFields($fields)
	{
		$this->person_fields = $fields;

		$custom_fields = new\Orb\Form\Field\FieldGroup(array('name' => 'custom_fields'));

		foreach ($fields as $field_def) {
			$form_field = $field_def->getHandler()->getFormField();
			$form_field->setOption('field_def', $field_def);
			$custom_fields->addField($form_field);
		}

		$this->addField($custom_fields);
	}

	public function getCustomFields()
	{
		return $this->person_fields;
	}

	public function setPerson(Person $person)
	{
		// Set basic values
		$simple = array('first_name', 'last_name', 'name', 'password');
		$values = array();
		foreach ($simple as $k) {
			$values[$k] = $person[$k];
		}

		$this->getField('basic_fields')->setData($values);

//		foreach ($person['fields'] as $fielddata) {
//			$name = 'field_' . $fielddata['person_field_id'];
//			if (isset($this['custom_fields'][$name])) {
//				$this['custom_fields'][$name]->setData($fielddata['data']);
//			}
//		}
	}

	public function savePerson(Person $person)
	{
		$em = App::getOrm();
		$em->beginTransaction();

		#------------------------------
		# Basic fields
		#------------------------------

		foreach ($this['basic_fields'] as $k => $f) {
			$person[$k] = $f->getData();
		}

		#------------------------------
		# Emails
		#------------------------------

		// Delete them, if they're checked
		foreach ($this['del_emails']->getData() as $email_id) {
			$person->removeEmailAddressId($email_id);
		}

		// Add them
		if ($this['new_email']->getData()) {
			$email = new PersonEmail();
			$email['is_validated'] = true;
			$email['email'] = $this['new_email']->getData();
			$person->addEmailAddress($email);
		}

		#------------------------------
		# Custom fields
		#------------------------------

//		foreach ($this['custom_fields'] as $k => $f) {
//
//			$value = $f->getData();
//
//			$field_def = $f->getOption('field_def');
//			$field_data = $person->getField($field_def['id']);
//
//			// A value exists, store it
//			if ($value !== null) {
//				if (!$field_data) {
//					$field_data = new PersonFieldData();
//					$field_data['field'] = $field_def;
//					$person->addFieldData($field_data);
//				}
//
//				$field_def->getHandler()->setValueOnData($field_data, $value);
//
//				$em->persist($field_data);
//
//			// A value doesnt exist, remove it
//			} else {
//				if ($field_data) {
//					$person['field_data']->remove($field_data);
//					$em->remove($field_data);
//				}
//			}
//		}

		$em->persist($person);
		$em->flush();
		$em->commit();
	}
}
