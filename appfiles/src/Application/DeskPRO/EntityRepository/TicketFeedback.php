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

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;
use Application\DeskPRO\Entity\TicketFeedback as TicketFeedbackEntity;
use \Doctrine\ORM\EntityRepository;

class TicketFeedback extends EntityRepository
{
	/**
	 * Get a feedback object for a message by a given person.
	 */
	public function getFeedback(TicketMessageEntity $message, PersonEntity $person, $create_if_notexist = false)
	{
		try {
			$feedback = $this->getEntityManager()->createQuery("
				SELECT f
				FROM DeskPRO:TicketFeedback f
				WHERE f.ticket = ?1 AND f.message = ?2 AND f.person = ?3
			")->setParameter(1, $message->ticket)
			  ->setParameter(2, $message)
			  ->setParameter(3, $person)
			  ->getSingleResult();
		} catch (\Exception $e) {
			$feedback = null;
		}

		if (!$feedback AND $create_if_notexist) {
			$feedback = new TicketFeedbackEntity();
			$feedback->ticket = $message->ticket;
			$feedback->ticket_message = $message;
			$feedback->person = $person;
		}

		return $feedback;
	}
}