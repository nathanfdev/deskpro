<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

/**
 * Splits a ticket from one message and on into a new ticket
 */
class TicketSplit
{

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 */
	protected $message;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(TicketMessage $message)
	{
		$this->em = App::getOrm();

		$this->message = $message;
	}


	public function split()
	{
		$ticket = $this->message->ticket;

		$messages = $this->em->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			WHERE m.id >= ?1 AND m.ticket = ?2
		")->execute(array(1=> $this->message['id'], 2=> $ticket['id']));

		$new_ticket = $ticket->copy();
		foreach ($messages as $m) {
			$ticket->messages->removeElement($m);
			$new_ticket->addMessage($m);

			foreach ($m->attachments as $attach) {
				$attach->ticket = $new_ticket;
			}
		}

		$new_ticket['creation_system'] = Ticket::CREATED_WEB_AGENT;

		$new_ticket->resetTicketLogger();// we dont want any of the usual logs to do with new items etc
		$new_ticket->getTicketLogger()->recordExtra('ticket_split', array('old_ticket' => $ticket)); // just the split

		$this->em->persist($ticket);
		$this->em->persist($new_ticket);
		$this->em->flush();

		return $new_ticket;
	}
}