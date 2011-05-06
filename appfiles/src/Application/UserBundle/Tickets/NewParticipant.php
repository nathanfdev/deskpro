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
		$ticket = $this->ticket;
		$part_person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($this->email);

		if (!$part_person) {
			$part_person = new Person();
			$part_person->addEmailAddressString($this->email);
			$part_person['first_name'] = $this->first_name;
			$part_person['last_name'] = $this->last_name;
		}
		
		if (!$part_person['first_name'] AND $this->first_name) {
			$part_person['first_name'] = $this->first_name;
		}
		if (!$part_person['last_name'] AND $this->last_name) {
			$part_person['last_name'] = $this->last_name;
		}

		$part_email = $part_person->findEmailAddress($this->email);

		$ticket->addParticipant($part_person);

		App::getOrm()->transactional(function($em) use ($part_person, $part, $ticket) {
			$em->persist($part_person);
			$em->persist($ticket);
			$em->flush();
		});
	}
}