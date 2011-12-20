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

class CategoryAction implements ActionInterface
{
	protected $category_id;

	public function __construct($category)
	{
		$this->category_id = $category;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$ticket['category_id'] = $this->category_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket['category_id'] == $this->category_id) {
			return array();
		}

		return array(
			array('action' => 'category', 'category_id' => $this->category_id)
		);
	}


	/**
	 * Get the category id
	 *
	 * @return int
	 */
	public function getCategoryId()
	{
		return $this->category_id;
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
		if ($this->category_id == 0) {
			return 'Remove category';
		} else {
			$names = App::getEntityRepository('DeskPRO:TicketCategory')->getFullCategoryNames();
			if (!isset($names[$this->category_id])) return '';

			return 'Set category to ' . $names[$this->category_id];
		}
	}
}
