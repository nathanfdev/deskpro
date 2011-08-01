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

use Orb\Util\Arrays;

/**
 * Removes labels
 */
class RemoveLabelsAction implements ActionInterface
{
	protected $remove_labels;

	public static function newFromString($remove_labels)
	{
		$remove_labels = explode(',', $remove_labels);

		return new self($remove_labels);
	}

	public function __construct(array $remove_labels)
	{
		array_walk($remove_labels, 'trim');
		$remove_labels = Arrays::removeEmptyString($remove_labels);
		
		$this->remove_labels = $remove_labels;
	}

	
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (!$this->remove_labels) {
			return;
		}

		$ticket->getLabelManager()->removeLabels($this->remove_labels);
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if (!$ticket->getLabelManager()->hasLabel($this->add_labels)) {
			return array();
		}

		return array(
			array('action' => 'remove_labels', 'label' => $this->add_labels)
		);
	}


	/**
	 * Get labels
	 * 
	 * @return array
	 */
	public function getLabels()
	{
		return $this->remove_labels;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$labels = array_merge($this->remove_labels, $other_action->getLabels());
		$labels = array_unique($labels);

		return new self($labels);
	}
}