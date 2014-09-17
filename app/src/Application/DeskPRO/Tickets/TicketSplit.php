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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\Ticket\ChangeSplitFrom;
use Application\DeskPRO\ORM\StateChange\Ticket\ChangeSplitTo;
use Application\DeskPRO\People\PersonContextInterface;

/**
 * Splits a ticket from one message and on into a new ticket
 */
class TicketSplit implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private  $em;

	/**
	 * @var TicketManager
	 */
	private $ticket_manager;

	/**
	 * @var Person
	 */
	private $person_context;


	/**
	 * @param Ticket $ticket
	 */
	public function __construct(Ticket $ticket)
	{
		$this->em = App::$container->getEm();
		$this->ticket_manager = App::$container->getTicketManager();
		$this->ticket = $ticket;
	}


	/**
	 * @param Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	/**
	 * @param string $subject
	 * @param array $message_ids
	 * @return Ticket
	 * @throws \Exception
	 */
	public function split($subject, array $message_ids)
	{
		if (!$message_ids) {
			throw new \InvalidArgumentException("No messages", 100);
		}

		if (!$this->person_context) {
			throw new \InvalidArgumentException("Missing person context", 300);
		}

		$this->ticket_manager->markAsManaged($this->ticket);
		try {
			$this->doSplit($subject, $message_ids);
			$this->ticket_manager->markAsUnmanaged($this->ticket);
		} catch (\Exception $e) {
			$this->ticket_manager->markAsUnmanaged($this->ticket);
			throw $e;
		}
	}


	/**
	 * @param string $subject
	 * @param array $message_ids
	 * @return Ticket
	 * @throws \InvalidArgumentException
	 */
	private function doSplit($subject, array $message_ids)
	{
		#------------------------------
		# Get and verify messages
		#------------------------------

		$messages = $this->em->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			WHERE m.id IN (?0) AND m.ticket = ?1
		")->execute(array($message_ids, $this->ticket->id));

		if (!count($messages)) {
			throw new \InvalidArgumentException("No messages", 100);
		}

		$count_all = $this->em->getConnection()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_messages
			WHERE ticket_id = ?
		", array($this->ticket->id));
		if ($count_all == count($messages)) {
			throw new \InvalidArgumentException("Cannot split the entire ticket", 200);
		}

		#------------------------------
		# Create new ticket copy
		#------------------------------

		$new_ticket = $this->ticket_manager->createTicket();
		$this->ticket->copyTo($new_ticket);

		$message_ids = array();

		$first = null;
		foreach ($messages as $m) {
			$message_ids[] = $m->id;
			$this->ticket->messages->removeElement($m);
			$new_ticket->addMessage($m);

			if (!$first) {
				$first = $m;
			}

			foreach ($m->attachments as $attach) {
				$attach->ticket = $new_ticket;
			}
		}

		if ($first) {
			$new_ticket->date_created = $first->date_created;
		}

		$new_ticket->creation_system = Ticket::CREATED_WEB_AGENT;

		if ($subject) {
			$new_ticket->subject = $subject;
		}

		$has_owner = false;
		foreach ($new_ticket->messages AS $message) {
			if ($message->person->id == $new_ticket->person->id) {
				$has_owner = true;
				break;
			}
		}

		if (count($messages) == 1 || !$has_owner) {
			$message = reset($messages);
			$new_ticket->person = $message->person;
			$new_ticket->person_email = $message->person->primary_email;
			$new_ticket->organization = $message->person->organization;
		}

		#------------------------------
		# Save new ticket
		#------------------------------

		$context = $this->ticket_manager->createAgentExecutorContext(
			$this->person_context,
			'update',
			'web'
		);

		$split_from_change = new ChangeSplitFrom('split_from', $this->ticket->id, $message_ids);
		$new_ticket->getStateChangeRecorder()->recordChange($split_from_change);

		$this->ticket_manager->saveTicket($new_ticket, $context);

		#------------------------------
		# Save old ticket
		#------------------------------

		$split_to_change = new ChangeSplitTo('split_to', $new_ticket->id, $message_ids);
		$this->ticket->getStateChangeRecorder()->recordChange($split_to_change);

		$this->ticket_manager->saveTicket($this->ticket, $context);


		return $new_ticket;
	}
}
