<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Email
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Email;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class TicketUtil
{
	private function __construct() { }

	/**
	 * Get a TAC for a person on a ticket. If an existing TAC doesn't exist,
	 * a new one will be created automatically.
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return \Application\DeskPRO\Entity\TicketAccessCode
	 */
	public static function getTacForPerson(Ticket $ticket, Person $person)
	{
		$tac = $ticket->findAccessCodeForPerson($person);
		if (!$tac) {
			$tac = $ticket->addAccessCodeForPerson($person);
			App::getOrm()->persist($tac);
			App::getOrm()->flush();
		}

		return $tac;
	}
}