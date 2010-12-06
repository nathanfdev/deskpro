<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;
use \Application\DeskPRO\Entity;

class TicketQueue extends EntityRepository
{
	/**
	 * Find all ticket queues that a person can see.
	 *
	 * @param mixed $person_id
	 * @return array
	 */
	public function getQueuesForPerson($person_id)
	{
		$persin_id = Person::smartPersonId($person_id);

		$filters = $this->getEntityManager()->createQuery("
			SELECT q
			FROM DeskPRO:TicketQueue q
			WHERE q.person_id = ?1 OR q.is_global = true
			ORDER BY q.title ASC
		")->setParameter(1, $person_id)->execute();

		return $fitlers;
	}

	public function getTicketQueueFromVar($var)
	{
		$ticket_queue_id = null;

		if (is_int($var) OR ctype_digit($var)) {
			$ticket_queue_id = (int)$var;
		} elseif (\is_object($var)) {
			if ($var instanceof Entity\TicketQueue) {
				return $var;
			}
		} elseif (isset($var['ticket_queue'])) {
			return $var['ticket_queue'];
		}

		if ($ticket_queue_id) {
			return $this->find($ticket_queue_id);
		}

		return null;
	}
}