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

		$f_group_props = new \Orb\Form\Field\FieldGroup(array('name' => 'field_properties'));

		// Typename
		if (!$this->formfield['id']) {
			$f = new \Orb\Form\Field\Hidden(array('name' => 'typeclass'));
			$f_group_props->addField($f);
		}

		// Title
		$f = new \Orb\Form\Field\Hidden(array('name' => 'title'));
		$f_group_props->addField($f);

		$this->addField($f_group_props);
	}
}