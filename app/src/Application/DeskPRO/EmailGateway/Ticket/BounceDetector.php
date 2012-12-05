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

use Orb\Log\Logger;
use Orb\Util\Strings;
use Application\DeskPRO\Entity\Ticket;

class BounceDetector extends \Application\DeskPRO\EmailGateway\BounceDetector
{
	/**
	 * @var string
	 */
	protected $ptac_code;


	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $guessed_ticket;

	/**
	 * @return string
	 */
	public function getPtacCode()
	{
		if ($this->ptac_code !== null) {
			if ($this->ptac_code === false) return null;
			return $this->ptac_code;
		}

		$m = null;
		if (preg_match('#PTAC\-([A-Z0-9]+)\.#', $this->reader->getRawHeaders(), $m)) {
			$this->ptac_code = $m[1];
			if ($this->logger) $this->logger->logDebug('Found PTAC: ' . $this->ptac_code);
		} else {
			$this->ptac_code = false;
			if ($this->logger) $this->logger->logDebug('No PTAC found');
		}

		return $this->ptac_code;
	}


	/**
	 * Try to guess the ticket this bounce belongs to
	 *
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function getGuessedTicket()
	{
		if ($this->guessed_ticket !== null) {
			if ($this->guessed_ticket === false) return null;
			return $this->guessed_ticket;
		}

		$this->guessed_ticket = false;

		if ($ptac = $this->getPtacCode()) {
			$ticket = $this->em->getRepository('DeskPRO:Ticket')->getByAccessCode($ptac);
			if ($ticket) {
				$this->guessed_ticket = $ticket;
				return $this->guessed_ticket;
			}
		}

		$guessed_emails = $this->getGuessedEmailAddresses();
		$found_ticket_id = null;
		$body = $this->reader->getBodyText()->getBodyUtf8();

		foreach ($guessed_emails as $email) {
			if ($this->logger) $this->logger->logDebug(sprintf("Finding last subjects by %s", $email));

			$ticket_subjects = $this->em->getConnection()->fetchAllKeyValue("
				SELECT tickets.id, tickets.subject
				FROM tickets
				LEFT JOIN people_emails ON (people_emails.person_id = tickets.person_id)
				WHERE tickets.status IN ('awaiting_user', 'awaiting_agent') AND people_emails.email = ?
				ORDER BY tickets.id DESC
				LIMIT 5
			", array($email));

			if ($this->original_subject) {
				foreach ($ticket_subjects as $tid => $subj) {
					if ($this->logger) $this->logger->logDebug(sprintf("Trying %d '%s' against original '%s'", $tid, $subj, $this->original_subject));
					if (strpos($this->original_subject, $subj) !== false) {
						$found_ticket_id = $tid;
						break 2;
					}
				}
			} else {
				foreach ($ticket_subjects as $tid => $subj) {
					if ($this->logger) $this->logger->logDebug(sprintf("Trying %d '%s' against body", $tid, $subj));
					if (strpos($body, $subj) !== false) {
						$found_ticket_id = $tid;
						break 2;
					}
				}
			}
		}

		if ($found_ticket_id) {
			$this->guessed_ticket = $this->em->find('DeskPRO:Ticket', $found_ticket_id);
			return $this->guessed_ticket;
		}

		return $this->guessed_ticket;
	}
}