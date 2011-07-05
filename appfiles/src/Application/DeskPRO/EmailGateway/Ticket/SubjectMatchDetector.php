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
 * Detects a ticket based off of a common subject and From email address.
 * For example "RE: Something"
 *
 * @see \Application\DeskPRO\Entity\TicketAccessCode
 */
class SubjectMatchDetector implements TicketDetectorInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $_found_person = null;

	protected $_time_cutoff = 0;

	/**
	 * @param int $time_cutoff Max age of a ticket before the subject match wont work
	 */
	public function __construct($time_cutoff = 604800 /* 7 days */)
	{
		$this->_time_cutoff = date('Y-m-d H:i:s', time()-$time_cutoff);
	}

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function findExistingTicket(AbstractReader $reader)
	{
		$this->_found_person = null;

		$subject = trim($reader->getSubject()->subject);

		// Strip off Re: prefix (and alternatives in some other langs)
		$subject = preg_replace('#^(RE|VS|AW|SV):\s*#i', '', $subject);
		$subject = trim($subject);

		// Now lets try to find it...
		$ticket_ids = App::getDb()->fetchAllCol("
			SELECT id
			FROM tickets
			WHERE subject = ? AND date_created > ?
			ORDER BY id DESC
			LIMIT 20
		", array($subject, $this->_time_cutoff));

		if (!$ticket_ids) return null;

		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);
		$from = $reader->getFromAddress()->email;

		foreach ($tickets as $ticket) {
			if ($p = $ticket->findUserByEmail($from)) {
				$this->_found_person = $p;
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
		if ($this->_found_person) {
			return $this->_found_person;
		}

		return null;
	}

	/**
	 * Unknown users cant be added based just on subject
	 *
	 * @return void
	 */
	public function canAddUnknownPerson()
	{
		return false;
	}
}