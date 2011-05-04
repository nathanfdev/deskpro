<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Person;

class NewParticipant
{
	public $first_name;
	public $last_name;
	public $email;

	protected $ticket;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function save()
	{
		$part_person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($this->email);

		if (!$part_person) {
			$person = Person::newContactPerson();
			$person->addEmailAddressString($this->email);
			$person['first_name'] = $this->first_name;
			$person['last_name'] = $this->last_name;
		}

		$part = $this->ticket->addParticipant($person);

		App::getOrm()->beginTransaction();
		if ($person->isNewPerson()) {
			App::getOrm()->persist($person);
		}
		App::getOrm()->persist($this->ticket);
		App::getOrm()->persist($part);
		App::getOrm()->flush();
		App::getOrm()->commit();
	}
}