<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Entity\Ticket;

use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\CustomDefTicket;

class TicketFieldAction implements ActionInterface
{
	/**
	 * @var \Application\DeskPRO\CustomFields\FieldManager
	 */
	protected $field_manager;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefTicket
	 */
	protected $field_def;

	/**
	 * @var mixed
	 */
	protected $set_value;

	public function __construct(FieldManager $field_manager, CustomDefTicket $field_def, $set_value)
	{
		$this->field_manager = $field_manager;
		$this->field_def     = $field_def;
		$this->set_value     = $set_value;
	}


	/**
	 * @return \Application\DeskPRO\Entity\CustomDefTicket
	 */
	public function getFieldDef()
	{
		return $this->field_def;
	}


	/**
	 * @return mixed
	 */
	public function getFieldValue()
	{
		return $this->set_value;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$this->field_manager->saveFormToObject($this->set_value, $ticket);
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		return array(
			array('action' => 'ticket_field', 'ticket_field_id' => $this->field_def->id, 'value' => $this->set_value)
		);
	}

	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		$title = $this->field_def->title;
		$value = $this->value;

		return "Set $title to $value";
	}
}
