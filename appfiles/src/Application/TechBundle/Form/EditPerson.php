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

use \Application\CoreBundle\Entity\FormField;
use \Application\CoreBundle\Entity\Person;

class EditPerson extends \Orb\Form\Field\Form
{
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
	}

	public function setCustomFields($fields)
	{
		$custom_fields = new\Orb\Form\Field\FieldGroup(array('name' => 'custom_fields'));

		foreach ($fields as $field) {
			$custom_fields->addField($field->getFormField());
		}

		$this->addField($custom_fields);
	}

	public function setPerson(Person $person)
	{
		// Set basic values
		$simple = array('full_name', 'informal_name', 'nick_name', 'username');
		$values = array();
		foreach ($simple as $k) {
			$values[$k] = $person[$k];
		}

		$this->getField('basic_fields')->setData($values);

		// Set custom fields
		if ($this->hasField('custom_fields')) {
			$values = array();
			foreach ($person->getFields() as $k => $obj) {
				$values['field_' . $k] = $obj['data'];
			}

			$this->getField('custom_fields')->setData($values);
		}
	}

	public function savePerson(Person $person)
	{
		foreach ($this['basic_fields'] as $k => $f) {
			$person[$k] = $f->getData();
		}
	}
}