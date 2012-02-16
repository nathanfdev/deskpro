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
 * This adds the ability to change the user owner of the ticket. But also
 * allows the option of adding the new user as a participant.
 */
class Person extends PropertyAbstract
{
	public function merge()
	{
		if ($this->strategy == self::STRATEGY_RIGHT AND $this->ticket->person != $this->other_ticket->person) {

			$old_person = $this->ticket->person;

			$this->ticket->person = $this->other_ticket->person;
			$this->ticket->person_email = $this->other_ticket->person_email;
			$this->ticket->organization = $this->other_ticket->organization;

			if ($this->getStrategyOption('add_follower') and $old_agent != $this->ticket->agent) {
				$this->ticket->addParticipantPerson($old_person);
			}
		}
	}
}