<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Form;

use \DeskPRO\App;

use \Application\CoreBundle\Entity\FormField;
use \Application\CoreBundle\Entity\PersonFieldData;
use \Application\CoreBundle\Entity\Person;

class EditPerson extends \Orb\Form\Field\Form
{
	protected $person_fields;

	protected function init()
	{
		$basic_fields = new\Orb\Form\Field\FieldGroup(array('name' => 'basic_fields'));

		$f = new \Orb\Form\Field\Text(array('name' => 'full_name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Text(array('name' => 'informal_name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Text(array('name' => 'nick_name'));
		$basic_fields->addField($f);

		$f = new \Orb\Form\Field\Text(array('name' => 'username'));
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
		$simple = array('full_name', 'informal_name', 'nick_name', 'username', 'password');
		$values = array();
		foreach ($simple as $k) {
			$values[$k] = $person[$k];
		}

		$this->getField('basic_fields')->setData($values);

		foreach ($person['fields'] as $fielddata) {
			$name = 'field_' . $fielddata['person_field_id'];
			if (isset($this['custom_fields'][$name])) {
				$this['custom_fields'][$name]->setData($fielddata['data']);
			}
		}
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

		foreach ($this['custom_fields'] as $k => $f) {

			$value = $f->getData();

			$field_def = $f->getOption('field_def');
			$field_data = $person->getField($field_def['id']);

			// A value exists, store it
			if ($value !== null) {
				if (!$field_data) {
					$field_data = new PersonFieldData();
					$field_data['field'] = $field_def;
					$person->addFieldData($field_data);
				}

				$field_def->getHandler()->setValueOnData($field_data, $value);
				
				$em->persist($field_data);

			// A value doesnt exist, remove it
			} else {
				if ($field_data) {
					$person['field_data']->remove($field_data);
					$em->remove($field_data);
				}
			}
		}

		$em->persist($person);
		$em->flush();
		$em->commit();
	}
}