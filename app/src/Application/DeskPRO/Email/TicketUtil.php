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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAccessCode;

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
		try {
			$tac = App::getOrm()->createQuery("
				SELECT t
				FROM DeskPRO:TicketAccessCode t
				WHERE t.ticket = ?1 AND t.person = ?2
			")->setParameters(array(1 => $ticket, 2 => $person))->getSingleResult();

			return $tac;
		} catch (\Exception $e) {

			$tac = new TicketAccessCode();
			$tac['ticket'] = $ticket;
			$tac['person'] = $person;
			$ticket->access_codes->add($tac);

			App::getOrm()->persist($tac);
			App::getOrm()->flush();

			return $tac;
		}
	}
}