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

namespace Application\DeskPRO\Form\ContactFieldHandler;

/**
 * Twitter field
 */
class Twitter extends AbstractContactFieldHandler
{
	protected $name_to_field = array(
		'twitter' => 'field_1',
	);

	public function getFormField()
	{
		$group = new \Orb\Form\Field\FieldGroup(array('name' => $this->getSimpleName()));
		$group->addTransformer($this);

		$f = new \Orb\Form\Field\Choice(array('name' => 'comment'));
		$f->addChoice('work', 'Work');
		$f->addChoice('personal', 'Personal');
		$f->addChoice('other', 'Other');
		$group->addField($f);

		// field_1 is the Twitter account name
		$f = new \Orb\Form\Field\Text(array('name' => 'twitter'));
		$group->addField($f);

		return $group;
	}
}