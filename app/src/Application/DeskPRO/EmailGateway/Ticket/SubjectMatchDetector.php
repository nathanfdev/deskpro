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

use Orb\Util\Strings;
use Orb\Log\Logger;
use Orb\Log\Loggable;

/**
 * Detects a ticket based off of a common subject and From email address.
 * For example "RE: Something"
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class SubjectMatchDetector implements TicketDetectorInterface, Loggable
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $_found_person = null;

	/**
	 * @var int
	 */
	protected $_time_cutoff = 0;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @var bool
	 */
	protected $is_bounce_mode = false;

	/**
	 * Enable bounce mode if the message is or is suspected ot be a bounced message.
	 * This will look for PTAC/TAC 'headers' in the body text.
	 */
	public function enableBouncedMode()
	{
		$this->is_bounce_mode = true;
	}

	/**
	 * @param int $time_cutoff Max age of a ticket before the subject match wont work
	 */
	public function __construct($time_cutoff = 7776000 /* 90 days */)
	{
		$this->_time_cutoff = date('Y-m-d H:i:s', time()-$time_cutoff);
	}

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$ticket = $this->_findExistingTicket($reader, $reader->getSubject()->getSubjectUtf8());

		if ($this->is_bounce_mode) {
			if ($reader->getOriginalSubject()) {
				$ticket = $this->_findExistingTicket($reader, $reader->getOriginalSubject()->getSubjectUtf8());
			}
		}

		if (!$ticket && $this->is_bounce_mode && $body = $reader->getBodyText()->getBody()) {
			$body_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $body);
			if ($body_subject) {
				$ticket = $this->_findExistingTicket($reader, $body_subject);
			}
		}

		return $ticket;
	}

	public function _findExistingTicket(AbstractReader $reader, $subject)
	{
		$this->getLogger()->logDebug("[SubjectMatchDetector] Finding ticket");

		$this->_found_person = null;

		$subject = trim($subject);
		$subject_orig = $subject;

		// Common prefixes
		// Also including FW|FWDxxx here to catch cases where a user uses fwd to reply to an email they just sent.
		if (!preg_match('#^(RE|VS|AW|SV|FW|FWD|VL|WG|FS|VB|RV|VS):\s*#i', $subject)) {
			return null;
		}

		// Strip off Re: prefix (and alternatives in some other langs)
		// The loop is so we can catch emails with multiple prefixes like RE: RE: RE:
		$last_subject = $subject_orig;
		$ticket_ids = null;
		while (true) {
			$subject_re   = preg_replace('#^(RE|VS|AW|SV|FW|FWD|VL|WG|FS|VB|RV|VS):\s*#i', '', trim($last_subject));
			$subject_re   = trim($subject_re);

			if ($subject_re == $last_subject) {
				break;
			}

			$last_subject = $subject_re;

			$this->getLogger()->logDebug("[SubjectMatchDetector] -- Trying to find subject: " . $subject_re);

			// Now lets try to find it...
			$ticket_ids = App::getDb()->fetchAllCol("
				SELECT id
				FROM tickets
				WHERE (subject = ?) AND date_created > ? AND status != 'closed'
				ORDER BY id DESC
				LIMIT 20
			", array($subject_re, $this->_time_cutoff));
		}

		if (!$ticket_ids) {
			$this->getLogger()->logDebug("[SubjectMatchDetector] -- Found nothing");
			return null;
		}

		$this->getLogger()->logDebug("[SubjectMatchDetector] -- Matching tickets: " . implode(', ', $ticket_ids));

		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);
		$from = $reader->getFromAddress()->getEmail();

		foreach ($tickets as $ticket) {
			if (($p = $ticket->findUserByEmail($from)) && (!$p->is_agent || $ticket->person->getId() == $p->getId())) {
				$this->getLogger()->logDebug("[SubjectMatchDetector] -- Found ticket " . $ticket->id . " with user " . $p->id);
				$this->_found_person = $p;
				return $ticket;
			}
		}

		$this->getLogger()->logDebug("[SubjectMatchDetector] -- Could not match user email address on ticket: " . $from);

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
	 * Unknown users cant be added based just on subject
	 *
	 * @return bool
	 */
	public function canAddUnknownPerson()
	{
		return false;
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
