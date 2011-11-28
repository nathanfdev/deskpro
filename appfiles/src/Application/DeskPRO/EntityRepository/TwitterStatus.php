<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

use Orb\Util\Numbers;

class TwitterStatus extends EntityRepository
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
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findMessagesForUserId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.recipient IS NOT NULL
			AND (s.user = :user_id OR s.recipient = :user_id)
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
	public function findRepliesForUserId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT r
			FROM DeskPRO:TwitterStatus r
			WHERE r.in_reply_to_status IN (
				SELECT s.id
				FROM DeskPRO:TwitterStatus s
				WHERE s.user = :user_id
			)
			ORDER BY r.date_created %s
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
	public function findMentionsForUserId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			LEFT JOIN s.mentions m
			WHERE m.user = :user_id
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
	 * @todo implement
	 */
	public function findRetweetsForUserId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
	 	return array();
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findOutgoingByUserId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.user = :user_id
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
	 * Get starred tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findStarredTweetsForAgentId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		var_dump($id);
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.user u
			INNER JOIN u.account a
			INNER JOIN a.persons p
			WHERE s.is_favorited = :is_favorited
			AND p.id = :agent_id
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.user u
			INNER JOIN u.account a
			WHERE s.is_favorited = :is_favorited
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				//'agent_id' => $id,
				'is_favorited' => true
			));
	}

	/**
	 * Get tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findTweetsForAgentId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			WHERE s.agent = :agent_id
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
	 * Get tweets for an agent team
	 *
	 * @param integer $id Agent Id
	 * @param string $sortByDate (optional)
	 * @param integer $limit (optional)
	 * @param integer $page (optional)
	 * @return array
	 */
	public function findTweetsForAgentTeamId($id, $sortByDate = 'ASC', $limit = 25, $page = 1)
	{
		$query = sprintf("
			SELECT s
			FROM DeskPRO:TwitterStatus s
			INNER JOIN s.person p
			INNER JOIN p.agent_teams at
			WHERE s.agent = :agent_id
			ORDER BY s.date_created %s
		", $this->normalizeSortByDate($sortByDate));

	 	return $this
			->getEntityManager()
			->createQuery($query)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute(array(
				'agent_team_id' => $id
			));
	}
}
