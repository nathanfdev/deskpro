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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Doctrine\ORM\EntityRepository;

use Orb\Util\Numbers;

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

		try {
			$message = $this->getEntityManager()->createQuery("
				SELECT m
				FROM DeskPRO:TicketMessage m
				WHERE m.ticket = ?1
				ORDER BY m.id ASC
			")->setParameter(1, $ticket)->setMaxResults(1)->getSingleResult();

			return $message;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}


	/**
	 * Get all messages in a ticket
	 * 
	 * @param  $ticket
	 * @return array
	 */
	public function getTicketMessages($ticket, array $set_options = array())
	{
		$options = array_merge(array(
			'order' => 'ASC',
			 'limit' => null,
			 'with_notes' => false,
			 'since_id' => 0
		), $set_options);

		$order = strtoupper($options['order']);
		if (!in_array($order, array('ASC', 'DESC'))) {
			$order = 'ASC';
		}

		$q = $this->getEntityManager()->createQueryBuilder();
		$q->from('DeskPRO:TicketMessage', 'm');
		$q->select('m');
		$q->leftJoin('m.person', 'p');
		$q->where('m.ticket = :ticket');
		$q->addOrderBy('m.id', $order);

		$params = array();
		$params['ticket'] = $ticket;

		if (isset($options['since_id']) && $options['since_id']) {
			$q->andWhere('m.id > :since_id');
			$params['since_id'] = $options['since_id'];
		}

		if (!$options['with_notes']) {
			$q->andWhere('m.is_agent_note = false');
		}

		if ($options['limit']) {
			$q->setMaxResults($options['limit']);
		}

		$q = $q->getQuery();

		$messages = $q->execute($params);

		return $messages;
	}


	/**
	 * Checks the database for a duplicate message.
	 *
	 * Returns the message ID if there was one found, or false if none found.
	 *
	 * @param \Application\DeskPRO\Entity\TicketMessage $message
	 * @param int $secs_ago
	 * @return bool|mixed
	 */
	public function checkDupeMessage(Entity\TicketMessage $message, $secs_ago = 10800 /* 3 hours */)
	{
		return false;
		$timesnip = date('Y-m-d H:m:s', time() - $secs_ago);

		$check = App::getDb()->fetchColumn("
			SELECT id
			FROM tickets_messages
			WHERE message_hash = ? AND date_created > ?
		", array($message['message_hash'], $timesnip));

		if ($check) {
			return $check;
		}

		return false;
	}
}