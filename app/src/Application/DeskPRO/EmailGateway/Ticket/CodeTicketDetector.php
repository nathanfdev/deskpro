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
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\Ticket;
use Orb\Log\Logger;
use Orb\Log\Loggable;

/**
 * Detects a ticket based off of codes in the subject or body.
 *
 * We look for (#AAAAA) in either the subject or body.
 * These are access codes that we can use to find a corresponding ticket and user.
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class CodeTicketDetector implements TicketDetectorInterface, Loggable
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
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->getLogger()->logDebug("[CodeTicketDetector] Finding ticket");

		$this->_found_person = null;

		$search_text = array();
		$search_text[] = $reader->getSubject()->subject;
		$search_text[] = $reader->getBodyText()->getBody();
		$search_text[] = strip_tags($reader->getBodyHtml()->getBody());

		$check_headers = array();
		if ($reader->getHeader('In-Reply-To')) {
			foreach ($reader->getHeader('In-Reply-To')->getAllParts() as $part) {
				$check_headers[] = $part;
			}
		}
		if ($reader->getHeader('References')) {
			foreach ($reader->getHeader('References')->getAllParts() as $part) {
				$check_headers[] = $part;
			}
		}

		// Add them to search text so below code will parse them out and treat them the same
		foreach ($check_headers as $header) {
			$m = null;
			if (preg_match('#PTAC\-([A-Za-z0-9]+)\.#', $header, $m)) {
				$this->getLogger()->logDebug("[CodeTicketDetector] Found PTAC in headers: " . $m[1]);
				$search_text[] = '(#' . $m[1] . ')';
			}
		}

		$search_text = implode(' ', $search_text);

		#------------------------------
		# TAC
		#------------------------------

		$auth_len = App::getSetting('core_tickets.ptac_auth_code_len');
		$authcode_min_len = $auth_len + 1;
		$authcode_max_len = $auth_len + 7;

		$matches = null;
		if (preg_match_all('/\(#([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\)/', $search_text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $m) {

				$this->getLogger()->logDebug("[CodeTicketDetector] Checking code that looks like TAC: {$m[1]}");

				$tac = App::getEntityRepository('DeskPRO:TicketAccessCode')->getTacArrayFromAccessCode($m[1]);
				if (!$tac) {
					$this->getLogger()->logDebug("[CodeTicketDetector] -- Invalid code");
					continue;
				}

				$this->getLogger()->logDebug("[CodeTicketDetector] -- Valid code");

				$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($tac['ticket_id']);
				if ($ticket && !$ticket->isArchived()) {
					$this->_found_person = App::getEntityRepository('DeskPRO:Person')->find($tac['person_id']);
					$this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person {$this->_found_person->id}");
					return $ticket;
				}
			}
		}

		#------------------------------
		# PTAC
		#------------------------------

		$matches = null;
		if (preg_match_all('/\(#([A-Z0-9]{'.$authcode_min_len.','.$authcode_max_len.'})\)/', $search_text, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $m) {

				$this->getLogger()->logDebug("[CodeTicketDetector] Checking code that looks like PTAC: {$m[1]}");

				$ticket = App::getEntityRepository('DeskPRO:Ticket')->getByAccessCode($m[1]);

				if ($ticket && !$ticket->isArchived()) {
					$this->_found_person = $ticket->findUserByEmail($reader->getFromAddress()->email);

					if ($this->_found_person) {
						$this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with person {$this->_found_person->id}");
					} else {
						$this->getLogger()->logDebug("[CodeTicketDetector] -- Matched ticket {$ticket->id} with new person");
					}

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
	 * Unknown people are added as CC's. If you know the P/TAC then it's as good as a passowrd.
	 *
	 * @return bool
	 */
	public function canAddUnknownPerson()
	{
		return true;
	}


	/**
	 * Set the logger
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		if (!$this->logger) {
			$this->logger = new Logger();
		}

		return $this->logger;
	}
}
