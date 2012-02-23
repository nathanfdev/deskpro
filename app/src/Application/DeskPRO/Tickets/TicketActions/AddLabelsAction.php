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
 * Adds labels
 */
class AddLabelsAction implements ActionInterface
{
	protected $add_labels;

	public static function newFromString($add_labels)
	{
		$add_labels = explode(',', $add_labels);

		return new self($add_labels);
	}

	public function __construct(array $add_labels)
	{
		array_walk($add_labels, 'trim');
		$add_labels = Arrays::removeEmptyString($add_labels);

		$this->add_labels = $add_labels;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (!$this->add_labels) {
			return;
		}

		$ticket->getLabelManager()->addLabels($this->add_labels);
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		$added_labels = array();
		foreach ($this->add_labels as $l) {
			if (!$ticket->getLabelManager()->hasLabel($l)) {
				$added_labels[] = $l;
			}
		}

		if (!$added_labels) {
			return array();
		}

		return array(
			array('action' => 'add_labels', 'label' => $added_labels)
		);
	}


	/**
	 * Get labels
	 *
	 * @return array
	 */
	public function getLabels()
	{
		return $this->add_labels;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		$labels = array_merge($this->add_labels, $other_action->getLabels());
		$labels = array_unique($labels);

		return new self($labels);
	}


	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		return "Add labels: " . implode($this->add_labels, ', ');
	}
}
