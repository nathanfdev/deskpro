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

		#------------------------------
		# Fetch PTAC from in-reply-to header
		#------------------------------

		$in_reply_to_objs = $reader->getHeader('In-Reply-To');
		if (!$in_reply_to_objs OR !$in_reply_to_objs->header_parts) return null;

		// If theres more than one, we'll just combine them into one string and
		// use whichever one first matches.
		
		$in_reply_to = array();
		foreach ($in_reply_to_objs->header_parts as $h) {
			$in_reply_to[] = $h;
		}

		$in_reply_to = implode(' ', $in_reply_to);

		$match_ptac = Strings::extractRegexMatch('#t([A-Z0-9]{6,11})@#', $in_reply_to, 1);
		if (!$match_ptac) return null;

		#------------------------------
		# Try to find the ticket and user now
		#------------------------------

		$ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($match_ptac);

		if ($ticket) {

			$this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

			return $ticket;
		}

		return null;
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


	/**
	 * Add unknown users, the reply code in the address is the PTAC
	 * so basically a passowrd
	 *
	 * @return void
	 */
	public function canAddUnknownPerson()
	{
		return true;
	}
}