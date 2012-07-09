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

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Orb\Log\Logger;
use Orb\Util\Strings;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;

class BounceDetector
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @var string[]
	 */
	protected $patterns;

	/**
	 * @var string
	 */
	protected $ptac_code;

	/**
	 * @var string
	 */
	protected $original_subject;

	/**
	 * @var string[]
	 */
	protected $guessed_email_addresses;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $guessed_ticket;

	public function __construct(AbstractReader $reader, EntityManager $em)
	{
		$this->reader = $reader;
		$this->em = $em;
	}

	/**
	 * @return string[]
	 */
	public function getPatterns()
	{
		if ($this->patterns !== null) {
			return $this->patterns;
		}

		$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('bounce-subject-patterns.php');
		$this->patterns = $pattern_config->all;

		return $this->patterns;
	}


	/**
	 * @return bool
	 */
	public function isBounced()
	{
		return false;
		$failed = $this->reader->getHeader('X-Failed-Recipients');
		if ($failed && $failed->getHeader()) {
			return true;
		}

		$subject = $this->reader->getSubject()->getSubjectUtf8();

		foreach ($this->getPatterns() as $pattern) {
			$m = null;
			if (preg_match($pattern, $subject, $m)) {
				if (isset($m['subject'])) {
					$this->original_subject = $m['subject'];
				}
				return true;
			}
		}

		return true;
	}


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
			$ticket_subjects = $this->em->getConnection()->fetchAllKeyValue("
				SELECT tickets.id, tickets.subject
				FROM tickets
				LEFT JOIN people_emails ON (people_emails.person_id = tickets.person_id)
				WHERE tickets.status IN ('awaiting_user', 'awaiting_agent') AND people_emails.email = ?
				LIMIT 3
			", array($email));

			if ($this->original_subject) {
				foreach ($ticket_subjects as $tid => $subj) {
					if (strpos($this->original_subject, $subj) !== false) {
						$found_ticket_id = $tid;
						break 2;
					}
				}
			} else {
				foreach ($ticket_subjects as $tid => $subj) {
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


	/**
	 * Try to find possible addresses to match on
	 *
	 * @return string[]
	 */
	public function getGuessedEmailAddresses()
	{
		if ($this->guessed_email_addresses !== null) {
			return $this->guessed_email_addresses;
		}

		$this->guessed_email_addresses = array();

		if ($failed = $this->reader->getHeader('X-Failed-Recipients')) {
			foreach ($failed->getAllParts() as $email) {
				$this->guessed_email_addresses[] = strtolower($email);
				if ($this->logger) $this->logger->logDebug('Found email via X-Failed-Recipients: ' . $email);
			}
		}

		// Find original message part
		$m = null;
		if (preg_match_all('#^To: (.*?)$#imu', $this->reader->getBodyText()->getBodyUtf8(), $m, \PREG_SET_ORDER)) {
			foreach ($m as $match) {
				$email = Strings::extractRegexMatch('#<(.*?)@(.*?)>#', $match[1]);
				if (!$email) {
					$email = Strings::extractRegexMatch('#(.*?)@(.*?)#', $match[1]);
				}

				if ($email) {
					$this->guessed_email_addresses[] = strtolower($email);
					if ($this->logger) $this->logger->logDebug('Found email via body: ' . $email);
				}
			}
		}

		return $this->guessed_email_addresses;
	}
}