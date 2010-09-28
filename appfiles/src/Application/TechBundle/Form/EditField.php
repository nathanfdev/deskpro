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

class EditField extends \Orb\Form\Field\Form
{
	/**
	 * The formfield we're working on
	 * @var Application\CoreBundle\Entity\FormField
	 */
	protected $formfield;

	protected function init()
	{
		if (!$this->hasOption('form_field') OR !($this->getOption('form_field') instanceof FormField)) {
			throw new InvalidArgumentException('Options must include a form_field item');
		}

		$this->formfield = $this->getOption('form_field');

		// Typename
		if (!$this->formfield['id']) {
			$f = new \Orb\Form\Field\Hidden(array('name' => 'typename'));
			$this->addField($f);
		}

		// Field options which are specific to types of fields
		$f = new \Orb\Form\Field\FieldGroup(array('name' => 'field_options'));
		$this->addField($f);

		switch ($this->formfield['typename']) {
			case 'text':               $this->_initTextFields(); break;
			case 'textarea':           $this->_initTextFields(); break;
		}

		// Set the data
		if ($this->formfield['id']) {
			$this->setData(array(
				'title' => $this->formfield['title'],
				'field_options' => $this->formfield['options']
			));
		}
	}

	protected function _initTextFields()
	{
		$f_opt = $this->getField('field_options');

		$f = new \Orb\Form\Field\Hidden(array('name' => 'min_length'));
		$f_opt->addField($f);

		$f = new \Orb\Form\Field\Hidden(array('name' => 'min_length'));
		$f_opt->addField($f);
	}

	public function applyFormToEntity(FormField $formfield = null)
	{
		if ($formfield === null) $formfield = $this->formfield;

		switch ($formfield['typename']) {
			case 'text':               $formfield['field_classname'] = 'Orb\\Form\\Field\\Text';
			case 'textarea':           $formfield['field_classname'] = 'Orb\\Form\\Field\\Textarea';
		}

		$formfield = $this->getField('field_options')->getData();
	}
}