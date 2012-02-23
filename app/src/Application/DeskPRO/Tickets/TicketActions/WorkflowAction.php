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

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

class WorkflowAction implements ActionInterface
{
	protected $workflow_id;

	public function __construct($workflow)
	{
		$this->workflow_id = $workflow;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$ticket['workflow_id'] = $this->workflow_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket['workflow_id'] == $this->workflow_id) {
			return array();
		}

		return array(
			array('action' => 'workflow', 'workflow_id' => $this->workflow_id)
		);
	}


	/**
	 * Get the workflow id
	 *
	 * @return int
	 */
	public function getWorkflowId()
	{
		return $this->workflow_id;
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
	public function getDescription($as_html = true)
	{
		if ($this->workflow_id == 0) {
			return 'Remove workflow';
		} else {
			$names = App::getEntityRepository('DeskPRO:TicketWorkflow')->getWorkflowNames();
			if (!isset($names[$this->workflow_id])) return '';

			return 'Set workflow to ' . $names[$this->workflow_id];
		}
	}
}
