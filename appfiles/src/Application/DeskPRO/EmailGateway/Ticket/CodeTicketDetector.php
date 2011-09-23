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

/**
 * Detects a ticket based off of codes in the subject or body.
 *
 * We look for (#AAAAA) in either the subject or body.
 * These are access codes that we can use to find a corresponding ticket and user.
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class CodeTicketDetector implements TicketDetectorInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketAccessCode
	 */
	protected $_found_tac = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $_found_person = null;

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->_found_tac = null;
		$this->_found_person = null;

		$search_text = array();
		$search_text[] = $reader->getSubject()->subject;
		$search_text[] = $reader->getBodyText()->getBody();
		$search_text[] = strip_tags($reader->getBodyHtml()->getBody());
		$search_text = implode(' ', $search_text);

		#------------------------------
		# TAC
		#------------------------------

		$matches = null;
		if (!preg_match_all('/\(#([A-Z0-9]{6,11})\)/', $search_text, $matches, PREG_SET_ORDER)) {
			return null;
		}

		foreach ($matches as $m) {
			$tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByAccessCode($m[1]);
			if (!$tac) continue;

			$ticket = $tac->ticket;

			$this->_found_tac = $tac;
			return $ticket;
		}

		#------------------------------
		# PTAC
		#------------------------------

		$matches = null;
		if (!preg_match_all('/\(#([A-Z0-9]{6,11})\)/', $search_text, $matches, PREG_SET_ORDER)) {
			return null;
		}

		foreach ($matches as $m) {
			$ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($m[1]);

			if ($ticket) {

				$this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

				return $ticket;
			}
		}

		return null;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function findExistingPerson(Ticket $ticket, AbstractReader $reader)
	{
		if ($this->_found_tac) {
			return $this->_found_tac->person;
		}
		if ($this->_found_person) {
			return $this->_found_person;
		}

		return null;
	}

	/**
	 * Unknow people are added as CC's. If you know the P/TAC then it's as good as a passowrd.
	 *
	 * @return bool
	 */
	public function canAddUnknownPerson()
	{
		return true;
	}
}
