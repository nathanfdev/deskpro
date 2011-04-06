<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Numbers;

class TicketMessage extends EntityRepository
{
	/**
	 * Fetch the first message of a ticket.
	 *
	 * @throws NoResultException If there is no message. This shouldn't happen
	 *                           because a ticket should always have a message. So it's quite exceptional indeed!
	 * @param int|Ticket $ticket A ticket ID or the ID of a ticket
	 * @return TicketMessage
	 */
	public function getFirstTicketMessage($ticket)
	{
		if (!($ticket instanceof Entity\Ticket)) {
			$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket);
		}

		$message = $this->getEntityManager()->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			WHERE m.ticket = ?1
			ORDER BY m.id ASC
		")->setParameter(1, $ticket)->setMaxResults(1)->getSingleResult();

		return $message;
	}


	/**
	 * Get all messages in a ticket
	 * 
	 * @param  $ticket
	 * @return array
	 */
	public function getTicketMessages($ticket)
	{
		$messages = $this->getEntityManager()->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			LEFT JOIN m.person p
			WHERE m.ticket = ?1
			ORDER BY m.id ASC
		")->setParameter(1, $ticket)->execute();

		return $messages;
	}
}