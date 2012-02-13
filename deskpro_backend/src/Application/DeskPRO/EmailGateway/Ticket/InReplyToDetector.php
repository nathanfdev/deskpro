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

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;

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
		# Fetch message Ids from headers
		#------------------------------

		$search_text = array();

		// In-Reply-To should have the direct message
		// being replied to
		$in_reply_to = $reader->getHeader('In-Reply-To');
		if ($in_reply_to) {
			foreach ($in_reply_to->getAllParts() as $part) {
				$search_text[] = $part;
			}
		}

		// References may have other messages in a thread,
		// so also a good place to look for the TAC
		$references = $reader->getHeader('References');
		if ($references) {
			foreach ($references->getAllParts() as $part) {
				$search_text[] = $part;
			}
		}

		$search_text = implode(' ', $search_text);

		#------------------------------
		# Try to find TAC
		#------------------------------

		$matches = null;
		if (preg_match_all('#(?<!P)TAC\-([A-Z0-9]{6,11})\.#i', $search_text, $matches, PREG_SET_ORDER)) {

			foreach ($matches as $m) {
				$tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByAccessCode($m[1]);
				if (!$tac) continue;

				$ticket = $tac->ticket;

				$this->_found_person = $tac->person;
				return $ticket;
			}
		}

		#------------------------------
		# Try to find PTAC
		#------------------------------

		$matches = null;
		if (preg_match_all('#PTAC\-([A-Z0-9]{6,11})\.#i', $search_text, $matches, PREG_SET_ORDER)) {

			foreach ($matches as $m) {
				$ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($m[1]);

				if ($ticket) {

					$this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

					return $ticket;
				}
			}
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
