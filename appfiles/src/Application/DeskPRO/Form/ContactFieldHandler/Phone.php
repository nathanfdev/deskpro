<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Form\ContactFieldHandler;

/**
 * Phone field
 */
class Phone extends AbstractContactFieldHandler
{
	protected $name_to_field = array(
		'country_code' => 'field_1',
		'phone' => 'field_2'
	);

	public function getFormField()
	{
		$group = new \Orb\Form\Field\FieldGroup(array('name' => $this->getSimpleName()));
		$group->addTransformer($this);

		$f = new \Orb\Form\Field\Choice(array('name' => 'comment'));
		$f->addChoice('work', 'Work');
		$f->addChoice('mobile', 'Mobile');
		$f->addChoice('home', 'Home');
		$f->addChoice('skype', 'Skype');
		$f->addChoice('fax', 'Fax');
		$f->addChoice('other', 'Other');
		$group->addField($f);

		// field_1 is the country code
		$f = new \Orb\Form\Field\Text(array('name' => 'country_code', 'size' => 4));
		$group->addField($f);

		// field_2 is the number
		$f = new \Orb\Form\Field\Text(array('name' => 'phone'));
		$group->addField($f);

		return $group;
	}
}