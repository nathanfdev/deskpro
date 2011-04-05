<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use \Application\DeskPRO\App;
use \Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use \Application\DeskPRO\Entity\Ticket;

use Orb\Util\Strings;

/**
 * Detects a ticket based off of In-Reply-To field and the From: must
 * be from a user we know about.
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class InReplyToDetector implements TicketDetectorInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketAccessCode
	 */
	protected $_found_person = null;

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->_found_person = null;
		$in_reply_to_objs = $reader->getHeader('In-Reply-To');
		if (!$in_reply_to_objs OR !$in_reply_to_objs->header_parts) null;

		$in_reply_to = array();
		foreach ($in_reply_to_objs->header_parts as $h) {
			$in_reply_to[] = $h;
		}

		$in_reply_to = implode(' ', $in_reply_to);
		
		$match_ref = Strings::extractRegexMatch('#ticket\-(.*?)@#', $in_reply_to, 1);
		if (!$match_ref) return null;

		$ticket = null;
		try {
			$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($match_ref);
		} catch (\Exception $e) {}

		if (!$ticket) return null;

		$person = $ticket->person;

		if (!$person->findEmailAddress($reader->getFromAddress()->email)) {
			return null;
		}

		$this->_found_person = $person;

		return $ticket;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
	{
		if ($this->_found_person) {
			return $this->_found_person;
		}

		return null;
	}
}