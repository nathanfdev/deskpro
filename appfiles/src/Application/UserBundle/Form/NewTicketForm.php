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

/**
 * The new ticket form
 */
class NewTicketForm extends \Symfony\Component\Form\Form
{
	/**
	 * The actual person (logged in)
	 */
	protected $person;

	/**
	 * A person object we'll use for things like permissions.
	 * So if the person is a guest, then this is a guest object
	 * with basic properties.
	 */
	protected $mock_person;

	protected $ticket_options;
	protected $ticket_fields;

	protected function configure()
	{
		$this->configurePersonForm();
		$this->configureTicketForm();
	}



	/**
	 * Configures the person form
	 */
	protected function configurePersonForm()
	{
		$this->addOption('person');

		$this->person = $this->getOption('person');

		if ($this->person AND $this->person['id']) {
			$this->mock_person = $this->person;
		} else {
			$this->person = null;
			
			// We need this for some things to get basic permissions
			$this->mock_person = Entity\Person::newContactPerson();
		}

		$form = new Form\Form('person');
		$this->add($form);


		#------------------------------
		# Standard fields
		#------------------------------

		$form->add(new Form\TextField('first_name'));
		$form->add(new Form\TextField('last_name'));

		if (!$this->person) {
			$form->add(new Form\TextField('email'));
		}
	}


	/**
	 * Configures the ticket form
	 */
	protected function configureTicketForm()
	{
		$form = new Form\Form('ticket');
		$this->add($form);

		#------------------------------
		# Standard fields
		#------------------------------

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->mock_person);

		$this->ticket_options = $ticket_options;

		$form->add(new Form\TextField('subject'));
		$form->add(new Form\TextareaField('message'));

		if (!empty($ticket_options['departments_hierarchy'])) {
			$form->add(new Form\ChoiceField('department_id', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['departments_hierarchy'], 'id', 'title')
			)));
		}

		if (!empty($ticket_options['ticket_categories_hierarchy'])) {
			$form->add(new Form\ChoiceField('category_id', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['ticket_categories_hierarchy'], 'id', 'title')
			)));
		}

		if (!empty($ticket_options['priorities'])) {
			$form->add(new Form\ChoiceField('priority_id', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['priorities'], '', '')
			)));
		}

		if (!empty($ticket_options['products'])) {
			$form->add(new Form\ChoiceField('product_id', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['products'], '', '')
			)));
		}	

		#------------------------------
		# Custom fields
		#------------------------------

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields_form = new Form\Form('custom_fields');
		$form->add($custom_fields_form);

		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $custom_fields_form);
		$this->ticket_fields = $custom_fields;
	}

	public function getTicketOptions()
	{
		return $this->ticket_options;
	}

	public function getTicketFields()
	{
		return $this->ticket_fields;
	}
}