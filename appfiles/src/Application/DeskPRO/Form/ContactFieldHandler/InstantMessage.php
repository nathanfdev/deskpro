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
 * IM field
 */
class InstantMessage extends AbstractContactFieldHandler
{
	protected $name_to_field = array(
		'im' => 'field_1',
		'protocol' => 'field_2'
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

		// field_1 is the ID field
		$f = new \Orb\Form\Field\Text(array('name' => 'im'));
		$group->addField($f);

		// field_2 is the protocol selector
		$f = new \Orb\Form\Field\Choice(array('name' => 'protocol'));
		$f->addChoice('aim', 'AIM');
		$f->addChoice('msn', 'MSN');
		$f->addChoice('icq', 'ICQ');
		$f->addChoice('jabber', 'Jabber');
		$f->addChoice('skype', 'Skype');
		$f->addChoice('gtalk', 'Google Talk');
		$f->addChoice('other', 'Other');
		$group->addField($f);

		return $group;
	}
}