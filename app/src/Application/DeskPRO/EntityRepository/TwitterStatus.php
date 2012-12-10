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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use \Application\DeskPRO\Entity\TwitterAccount AS TwitterAccountEntity;

use Orb\Util\Numbers;

class TwitterStatus extends AbstractEntityRepository
{
	public function getByTwitterStatusId($id)
	{
		return $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.id = ?0
		")->setParameters(array($id))->getOneOrNullResult();
	}

	/**
	 * @param integer $id
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findMessagesForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.recipient IS NOT NULL
			AND (s.user = :user_id OR s.recipient = :user_id)
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= sprintf("
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

		return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param integer $id
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findOutgoingForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.user = :user_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= sprintf("
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

		return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param integer $id
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findRepliesForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		$query = "
			SELECT r
			FROM DeskPRO:TwitterStatus r
			WHERE r.in_reply_to_status IN (
				SELECT s.id
				FROM DeskPRO:TwitterStatus s
				WHERE s.user = :user_id
			)
		";

		if (!$includeArchived) {
			$query .= " AND r.is_archived = 0 ";
		}

		$query .= sprintf("
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

		return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findMentionsForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			LEFT JOIN s.mentions m
			WHERE m.user = :user_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= sprintf("
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param string $sortByDate (optional)
	 * @return string
	 */
	protected function normalizeSortByDate($sortByDate = 'asc')
	{
		// check that sort by date is asc or desc
		if (!in_array(strtolower($sortByDate), array('asc', 'desc'))) {
			$sortByDate = 'asc';
		}

		return strtoupper($sortByDate);
	}


	/**
	 * @param integer $limit
	 * @param integer $page
	 * @return integer
	 */
	protected function calculateOffset($limit, $page)
	{
		if (1 <= $page) {
			$page = 0;
		}

		return $page * $limit;
	}
}
