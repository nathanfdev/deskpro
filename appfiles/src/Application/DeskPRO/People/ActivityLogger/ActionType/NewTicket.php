<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

class NewTicket extends ActionTypeAbstract
{
	protected $ticket;


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function __construct(Person $person, Ticket $ticket)
	{
		$this->person = $person;
		$this->ticket = $ticket;
	}

	
	/**
	 * Get a plain array of details that'll be stored in the databaes
	 * @return array
	 */
	public function getDetails()
	{
		return array(
			'ticket_id' => $this->ticket['id'],
			'subject' => $this->ticket['subject'],
		);
	}
}