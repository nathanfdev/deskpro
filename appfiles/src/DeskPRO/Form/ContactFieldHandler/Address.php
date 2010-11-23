<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Form\ContactFieldHandler;

/**
 * An Address field
 */
class Address extends AbstractContactFieldHandler
{
	protected $name_to_field = array(
		'address' => 'field_1',
		'country' => 'field_2'
	);

	public function getFormField()
	{
		$group = new \Orb\Form\Field\FieldGroup(array('name' => $this->getSimpleName()));
		$group->addTransformer($this);

		$f = new \Orb\Form\Field\Choice(array('name' => 'comment'));
		$f->addChoice('work', 'Work');
		$f->addChoice('home', 'Home');
		$f->addChoice('other', 'Other');
		$group->addField($f);

		// field_1 is the main address field
		$f = new \Orb\Form\Field\Textarea(array('name' => 'address'));
		$group->addField($f);

		// field_2 is the country selector
		// TODO make it a select
		$f = new \Orb\Form\Field\Textarea(array('name' => 'country'));
		$group->addField($f);

		return $group;
	}
}