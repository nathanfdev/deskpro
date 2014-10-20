<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketFeedback as TicketFeedbackEntity;
use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;
use Orb\Util\Arrays;

class TicketFeedback extends AbstractEntityRepository
{
	/** @var int */
	protected $per_page = 10;

	/**
	 * Get a feedback object for a message by a given person.
	 */
	public function getFeedback(TicketMessageEntity $message, PersonEntity $person, $create_if_notexist = false)
	{
		$feedback = $this->getEntityManager()->createQuery("
				SELECT f
				FROM DeskPRO:TicketFeedback f
				WHERE f.ticket_message = ?0 AND f.person = ?1
			")->setParameter(0, $message)
			  ->setParameter(1, $person)
			  ->setMaxResults(1)
			  ->getOneOrNullResult();

		if (!$feedback AND $create_if_notexist) {
			$feedback = new TicketFeedbackEntity();
			$feedback->ticket = $message->ticket;
			$feedback->ticket_message = $message;
			$feedback->person = $person;
		}

		return $feedback;
	}


	/**
	 * @param TicketEntity $ticket
	 * @return array
	 */
	public function getFeedbackForTicket(TicketEntity $ticket)
	{
		$res = $this->getEntityManager()->createQuery("
			SELECT f
			FROM DeskPRO:TicketFeedback f
			WHERE f.ticket = ?1
		")->setParameter(1, $ticket)->execute();

		if (!$res) {
			return array();
		}

		$res = Arrays::keyFromData($res, 'message_id');
		return $res;
	}


	/**
	 * @param int $page
	 * @return mixed
	 */
	public function getFeedbackForFeed($page)
    {
        $query = $this->getEntityManager()->createQuery("
			SELECT f
			FROM DeskPRO:TicketFeedback f
			ORDER BY f.date_created DESC")
            ->setMaxResults($this->per_page)
            ->setFirstResult($page * $this->per_page);

        return $query->execute();
    }


	/**
	 * @return mixed
	 */
	public function getCountForPaging()
    {
        $query = $this->getEntityManager()->createQuery("
			SELECT COUNT(f)
			FROM DeskPRO:TicketFeedback f");

        return $query->execute();
    }


	/**
	 * @return float
	 */
	public function getFeedbackPagesCount()
	{
		$count = $this->getCountForPaging();

		return ceil($count[0][1] / $this->per_page);
	}


	/**
	 * @param PersonEntity $agent
	 * @param              $date_range
	 * @return array
	 */
	public function getFeedbackRatingsForAgent(PersonEntity $agent, $date_range)
    {
        $db = App::getDb();
		$result = $db->fetchAll('
			SELECT tf.rating AS rating
			FROM ticket_feedback AS tf
			INNER JOIN tickets_messages AS tm
			ON tf.ticket_id = tm.id
			WHERE tm.person_id = ?
			AND tf.date_created BETWEEN ? AND ?
		', array($agent['id'], $date_range['start'], $date_range['end']));

        return $result;
    }


	/**
	 * @return mixed
	 */
	public function getFirstCreatedDate()
    {
        $db = App::getDb();
        $result = $db->fetchColumn('SELECT MIN(date_created) FROM ticket_feedback');
        return $result;
    }
}
