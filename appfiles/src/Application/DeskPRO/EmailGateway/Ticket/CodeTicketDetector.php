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
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->_found_tac = null;

		$search_text = array();
		$search_text[] = $reader->getSubject()->subject;
		$search_text[] = $reader->getBodyText()->getBody();
		$search_text[] = strip_tags($reader->getBodyHtml()->getBody());
		$search_text = implode(' ', $search_text);

		$matches = null;
		if (!preg_match_all('/\(#([A-Z]{6,11})\)/', $search_text, $matches, PREG_SET_ORDER)) {
			return null;
		}

		foreach ($matches as $m) {
			$tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByAccessCode($m[1]);
			if (!$tac) continue;

			$ticket = $tac->ticket;

			$this->_found_tac = $tac;
			return $ticket;
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

		return null;
	}
}