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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The new ticket form
 */
class NewTicketType extends AbstractType
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
	protected $ticket_fields = array();

	public function __construct($person)
	{
		$this->person = $person;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$this->buildPersonForm($builder);
		$this->buildTicketForm($builder);
	}



	/**
	 * Configures the person form
	 */
	protected function buildPersonForm(FormBuilder $builder)
	{
		if ($this->person AND $this->person['id']) {
			$this->mock_person = $this->person;
		} else {
			$this->person = null;

			// We need this for some things to get basic permissions
			$this->mock_person = Entity\Person::newContactPerson();
		}

		$person_builder = $builder->create('person', 'form')
			->add('name', 'text', array('data' => $this->mock_person['name']));

		if (!$this->person) {
			$person_builder->add('email', 'text');
		}
		$builder->add($person_builder);
	}


	/**
	 * Configures the ticket form
	 */
	protected function buildTicketForm(FormBuilder $builder)
	{
		$ticket_builder = $builder->create('ticket', 'form');

		#------------------------------
		# Standard fields
		#------------------------------

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->mock_person);

		$this->ticket_options = $ticket_options;

		$ticket_builder->add('subject', 'text');
		$ticket_builder->add('message', 'textarea');

		if (!empty($ticket_options['departments_hierarchy'])) {
			$ticket_builder->add('department_id', 'choice', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['departments_hierarchy'], 'id', 'title')
			));
		}

		#------------------------------
		# Custom fields
		#------------------------------

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();

		$ticket_fields_builder = $ticket_builder->create('custom_ticket_fields', 'form');

		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $ticket_fields_builder);
		$this->ticket_fields = $custom_fields;

		$builder->add($ticket_builder);
		$builder->add($ticket_fields_builder);
	}

	public function getTicketOptions()
	{
		return $this->ticket_options;
	}

	public function getTicketFields()
	{
		return $this->ticket_fields;
	}

	public function getName()
	{
		return 'newticket';
	}
}
