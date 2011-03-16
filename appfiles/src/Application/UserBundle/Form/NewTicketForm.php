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

namespace Application\UserBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

class NewTicketForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->setDataClass('Application\\UserBundle\\NewTicket');

		#------------------------------
		# User fields
		#------------------------------

		$person_form = new Form\Form('person');
		$this->add($person_form);

		$this->addOption('person');
		$person = $this->getOption('person', array('property_path' => 'person_values'));

		$person_form->add(new Form\TextField('first_name'));
		$person_form->add(new Form\TextField('last_name'));

		if (!$person OR !$person['id']) {
			$this->add(new Form\TextField('email_address', array(
				'property_path' => 'email_address'
			)));
		}

		#------------------------------
		# Ticket fields
		#------------------------------

		$this->addRequiredOption('ticket_options');
		$this->addRequiredOption('custom_fields');

		$ticket_form = new Form\Form('ticket', array('property_path' => 'ticket_values'));
		$this->add($ticket_form);

		$ticket_form->add(new Form\TextField('subject'));

		$ticket_options = $this->getOption('ticket_options');
		if (!empty($ticket_options['departments_hierarchy'])) {
			$ticket_form->add(new Form\ChoiceField('department_id', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['departments_hierarchy'], 'id', 'title')
			)));
		}

		if (!empty($ticket_options['ticket_categories_hierarchy'])) {
			$ticket_form->add(new Form\ChoiceField('category_id', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['ticket_categories_hierarchy'], 'id', 'title')
			)));
		}

		if (!empty($ticket_options['priorities'])) {
			$ticket_form->add(new Form\ChoiceField('priority_id', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['priorities'], '', '')
			)));
		}

		if (!empty($ticket_options['products'])) {
			$ticket_form->add(new Form\ChoiceField('product_id', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['products'], '', '')
			)));
		}

		if ($this->getOption('custom_fields')) {
			$ticket_form->add($this->getOption('custom_fields'));
		}

		#------------------------------
		# TIcket message
		#------------------------------

		$this->add(new Form\TextareaField('ticket_message', array('property_path' => 'ticket_message')));

	}
}