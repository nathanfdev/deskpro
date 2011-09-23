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
 * Detects a ticket based off of REF codes in the subject
 */
class SubjectRefMatchDetector implements TicketDetectorInterface
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

		$ticket_refs = App::get('deskpro.ref_generator')->extractRefs($subject);
		if (!$ticket_refs) return null;

		foreach ($ticket_refs as $ref) {
			try {
				$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ref);
			} catch (\Exception $e) {
				continue;
			}

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
