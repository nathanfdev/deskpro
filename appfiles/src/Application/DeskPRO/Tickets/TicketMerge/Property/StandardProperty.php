<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;


use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

use Orb\Util\Arrays;

/**
 * A standard property on a ticket where only one value can exist.
 *
 * The folowing properties are accepted:
 * - department
 * - category
 * - priority
 * - workflow
 * - product
 * - status
 * - urgency
 * - subject
 */
class StandardProperty extends PropertyAbstract
{
	/**
	 * @var string
	 */
	protected $property;

	public function setProperty($property)
	{
		$this->property = $property;
	}

	public function merge()
	{
		if ($this->strategy == self::STRATEGY_RIGHT) {
			$this->ticket[$this->property] = $this->other_ticket[$this->property];

			if ($this->property == 'status' && $this->other_ticket[$this->property] == 'hidden') {
				$this->ticket['hidden_status'] = $this->other_ticket[$this->property] == 'hidden';
			}
		}
	}
}