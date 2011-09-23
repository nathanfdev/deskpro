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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The edit ticket form
 */
class EditTicketType extends AbstractType
{
	protected $person;
	protected $ticket_options;
	protected $ticket_fields = array();

	public function __construct($person)
	{
		$this->person = $person;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$ticket_builder = $builder->create('ticket', 'form');

		#------------------------------
		# Standard fields
		#------------------------------

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$this->ticket_options = $ticket_options;

		$ticket_builder->add('subject', 'text');

		if (!empty($ticket_options['departments_hierarchy'])) {
			$ticket_builder->add('department_id', 'choice', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['departments_hierarchy'], 'id', 'title'),
				'required' => false
			));
		}

		if (!empty($ticket_options['ticket_categories_hierarchy'])) {
			$ticket_builder->add('category_id', 'choice', array(
				'choices' => Arrays::selectArrayFromHierarchy($ticket_options['ticket_categories_hierarchy'], 'id', 'title'),
				'required' => false
			));
		}

		if (!empty($ticket_options['priorities'])) {
			$ticket_builder->add('priority_id', 'choice', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['priorities'], '', ''),
				'required' => false
			));
		}

		if (!empty($ticket_options['products'])) {
			$ticket_builder->add('product_id', 'choice', array(
				'choices' => Arrays::unshiftAssocReturn($ticket_options['products'], '', ''),
				'required' => false
			));
		}

		#------------------------------
		# Custom fields
		#------------------------------

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();

		$ticket_fields_builder = $ticket_builder->create('custom_fields', 'form');

		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $ticket_fields_builder);
		$this->ticket_fields = $custom_fields;

		$builder->add($ticket_builder);
		$ticket_builder->add($ticket_fields_builder);
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
		return 'editticket';
	}
}
