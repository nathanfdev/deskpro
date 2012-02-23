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

class ProductAction implements ActionInterface
{
	protected $product_id;

	public function __construct($product)
	{
		$this->product_id = $product;
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$ticket['product_id'] = $this->product_id;
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if ($ticket['product_id'] == $this->product_id) {
			return array();
		}

		return array(
			array('action' => 'product', 'product_id' => $this->product_id)
		);
	}


	/**
	 * Get the product id
	 *
	 * @return int
	 */
	public function getProductId()
	{
		return $this->product_id;
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
		if ($this->product_id == 0) {
			return 'Remove product';
		} else {
			$names = App::getEntityRepository('DeskPRO:Product')->getFullProductNames();
			if (!isset($names[$this->product_id])) return '';

			return 'Set product to ' . $names[$this->product_id];
		}
	}
}
