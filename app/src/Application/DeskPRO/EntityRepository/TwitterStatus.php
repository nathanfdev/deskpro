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

use \Doctrine\ORM\EntityRepository;

use Orb\Util\Numbers;

class TwitterStatus extends AbstractEntityRepository
{
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

	/**
	 * @param array $userIds An array of TwitterUser ids
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findByUserIds(array $userIds, $includeArchived = false, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$userIds = array_filter($userIds, function ($value) {
			if (Numbers::isInteger($value)) {
				return true;
			}

			return false;
		});

		if (!$userIds) {
			return array();
		}

		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.user IN (".implode(',', $userIds).")
			AND s.recipient IS NULL
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= sprintf(" ORDER BY s.date_created %s", $this->normalizeSortByDate($sortByDate));

		$statuses = $this->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute();

		return $statuses;
	}

	/**
	 * @param array $userIds An array of TwitterUser ids
	 * @param Boolean $includeArchived (optional)
	 * @return int
	 */
	public function countByUserIds(array $userIds, $includeArchived = false)
	{
		$userIds = array_filter($userIds, function ($value) {
			if (Numbers::isInteger($value)) {
				return true;
			}

			return false;
		});

		if (!$userIds) {
			return 0;
		}

		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			WHERE s.user IN (".implode(',', $userIds).")
			AND s.recipient IS NULL
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		return $this
			->getEntityManager()
			->createQuery($query)
			->getSingleScalarResult();
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
	 * @return array
	 */
	public function countMessagesForUserId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			WHERE s.recipient IS NOT NULL
			AND (s.user = :user_id OR s.recipient = :user_id)
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		return $this
			->getEntityManager()
			->createQuery($query)
			->setParameters(array(
				'user_id' => $id
			))
			->getSingleScalarResult();
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
	 * @param Boolean $includeArchived (optional)
	 * @return int
	 */
	public function countRepliesForUserId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(r.id)
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

		return $this
			->getEntityManager()
			->createQuery($query)
			->setParameters(array(
				'user_id' => $id
			))
			->getSingleScalarResult();
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
	 * @param integer $id
	 * @return int
	 */
	public function countMentionsForUserId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			LEFT JOIN s.mentions m
			WHERE m.user = :user_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setParameters(array(
				'user_id' => $id
			))
			->getSingleScalarResult();
	}

	/**
	 * @todo implement
	 */
	public function findRetweetsForUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
	{
	 	return array();
	}

	/**
	 * @todo implement
	 */
	public function countRetweetsForUserId($id, $includeArchived = false)
	{
	 	return 0;
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findOutgoingByUserId($id, $includeArchived = false, $sortByDate = 'asc', $limit = 25, $page = 1)
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
	 * @return array
	 */
	public function countOutgoingByUserId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			WHERE s.user = :user_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setParameters(array(
				'user_id' => $id
			))
			->getSingleScalarResult();
	}

	/**
	 * Get starred tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @param Boolean $includeAccount (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findStarredTweetsForAgentId($id, $includeArchived = false, $includeAccount = false, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.user u
			INNER JOIN u.account a
			INNER JOIN a.persons p
			WHERE s.is_favorited = :is_favorited
			AND p.id = :agent_id
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
				'agent_id' => $id,
				'is_favorited' => true
			));
	}

	/**
	 * Count starred tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @return array
	 */
	public function countStarredTweetsForAgentId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.user u
			INNER JOIN u.account a
			INNER JOIN a.persons p
			WHERE s.is_favorited = :is_favorited
			AND p.id = :agent_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}
	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setParameter('agent_id', $id)
			->getSingleScalarResult();
	}

	/**
	 * Get tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @param Boolean $includeAccount (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findTweetsForAgentId($id, $includeArchived = false, $includeAccount = false, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.agent = :agent_id
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
				'agent_id' => $id
			));
	}

	/**
	 * Counts tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @return int
	 */
	public function countTweetsForAgentId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			WHERE s.agent = :agent_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setParameter('agent_id', $id)
			->getSingleScalarResult();
	}

	/**
	 * Get tweets for an agent team
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @param Boolean $includeAccount (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findTweetsForAgentTeamByAgentId($id, $includeArchived = false, $includeAccount = false, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.agent_team at
			INNER JOIN at.members m
			WHERE m.id = :agent_id
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
				'agent_id' => $id
			));
	}

	/**
	 * Count tweets for an agent team
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @return int
	 */
	public function countTweetsForAgentTeamByAgentId($id, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.agent_team at
			INNER JOIN at.members m
			WHERE m.id = :agent_id
		";

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setParameter('agent_id', $id)
			->getSingleScalarResult();
	}
}
