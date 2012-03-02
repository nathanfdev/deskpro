<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Email
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
