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

class TwitterAccountStatus extends AbstractEntityRepository
{
	const DEFAULT_LIMIT = 25;

	public function getByTwitterStatusAndAccount($id, TwitterAccountEntity $account)
	{
		return $this->getEntityManager()->createQuery("
			SELECT s, t, u
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.status t
			INNER JOIN t.user u
			WHERE s.status = ?0
				AND s.account = ?1
		")->setParameters(array($id, $account))->getOneOrNullResult();
	}

	public function getByTwitterIdsAndAccount(array $ids, TwitterAccountEntity $account)
	{
		if (!$ids) {
			return array();
		}

		$output = array();
		$results = $this->getEntityManager()->createQuery("
			SELECT s, t, u
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.status t
			INNER JOIN t.user u
			WHERE s.status IN (?0)
				AND s.account = ?1
		")->setParameters(array($ids, $account))->execute();

		foreach ($results AS $result) {
			$output[$result->status->id] = $result;
		}

		return $output;
	}

	public function getTimelineForAccount(TwitterAccountEntity $account, $type = 'all', $includeSelf = false, $includeArchived = false, $sortByDate = 'ASC', $page = 1, $limit = self::DEFAULT_LIMIT)
	{
		$query = "
			SELECT s, t, u, a
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.status t
			INNER JOIN t.user u
			INNER JOIN s.account a
			WHERE s.account = ?0
		";
		$params = array($account);
		$i = 1;

		if ($type == 'sent') {
			$includeSelf = true;
		}

		if (!$includeSelf) {
			$query .= " AND s.status_type <> 'sent'";
		}

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= " AND s.status_type IS NOT NULL";

		if (!$includeSelf) {
			$query .= " AND (s.status_type <> 'direct' OR t.user <> ?$i)";
			$params[] = $account->user->getId();
			$i++;
		}

		// note that sent should always be included - it will be filtered out above if needed
		switch ($type) {
			case 'timeline':
			case 'reply':
			case 'mention':
			case 'retweet':
				$query .= " AND s.status_type IN ('$type', 'sent')";
				break;

			case 'sent':
				// need to get sent DMs too
				$query .= " AND (s.status_type = 'sent' OR (s.status_type = 'direct' AND t.user = ?$i))";
				$params[] = $account->user->getId();
				$i++;
				break;

			case 'direct':
				// direct messages are separate from sent messages - both sides are tagged as direct
				$query .= " AND s.status_type = 'direct'";
				break;

			case 'inbox':
				$query .= " AND s.status_type IN ('reply', 'mention', 'retweet', 'direct', 'sent')";
				break;

			case 'all':
			default:
				// do nothing
		}

		$query .= sprintf(" ORDER BY s.date_created %s", $this->normalizeSortByDate($sortByDate));

		$statuses = $this->getEntityManager()
			->createQuery($query)
			->setParameters($params)
			->setMaxResults($limit)
			->setFirstResult($this->calculateOffset($limit, $page))
			->execute();

		return $statuses;
	}

	public function countTimelineForAccount(TwitterAccountEntity $account, $type = 'all', $includeSelf = false, $includeArchived = false)
	{
		$query = "
			SELECT COUNT(s.id)
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.status t
			WHERE s.account = ?0
		";
		$params = array($account);
		$i = 1;

		if ($type == 'sent') {
			$includeSelf = true;
		}

		if (!$includeSelf) {
			$query .= " AND s.status_type <> 'sent'";
		}

		if (!$includeArchived) {
			$query .= " AND s.is_archived = 0 ";
		}

		$query .= " AND s.status_type IS NOT NULL";

		if (!$includeSelf) {
			$query .= " AND (s.status_type <> 'direct' OR t.user <> ?$i)";
			$params[] = $account->user->getId();
			$i++;
		}

		// note that sent should always be included - it will be filtered out above if needed
		switch ($type) {
			case 'timeline':
			case 'reply':
			case 'mention':
			case 'retweet':
				$query .= " AND s.status_type IN ('$type', 'sent')";
				break;

			case 'sent':
				// need to get sent DMs too
				$query .= " AND (s.status_type = 'sent' OR (s.status_type = 'direct' AND t.user = ?$i))";
				$params[] = $account->user->getId();
				$i++;
				break;

			case 'direct':
				// direct messages are separate from sent messages - both sides are tagged as direct
				$query .= " AND s.status_type = 'direct'";
				break;

			case 'inbox':
				$query .= " AND s.status_type IN ('reply', 'mention', 'retweet', 'direct', 'sent')";
				break;

			case 'all':
			default:
				// do nothing
		}

		return $this->getEntityManager()->createQuery($query)->setParameters($params)->getSingleScalarResult();
	}

	/**
	 * Get starred tweets for an agent
	 *
	 * @param integer $id Agent Id
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @param integer $page (optional)
	 * @param integer $limit (optional)
	 * @return array
	 */
	public function findStarredTweetsForAgentId($id, $includeArchived = false, $sortByDate = 'ASC', $page = 1, $limit = self::DEFAULT_LIMIT)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.account a
			INNER JOIN a.persons p
			WHERE s.is_favorited = true
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
				'agent_id' => $id
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
			FROM DeskPRO:TwitterAccountStatus s
			INNER JOIN s.account a
			INNER JOIN a.persons p
			WHERE s.is_favorited = true
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
	 * @param string $sortByDate (optional)
	 * @param integer $page (optional)
	 * @param integer $limit (optional)
	 * @return array
	 */
	public function findTweetsForAgentId($id, $includeArchived = false, $sortByDate = 'ASC', $page = 1, $limit = self::DEFAULT_LIMIT)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterAccountStatus s
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
			FROM DeskPRO:TwitterAccountStatus s
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
	 * @param string $sortByDate (optional)
	 * @param integer $page (optional)
	 * @param integer $limit (optional)
	 * @return array
	 */
	public function findTweetsForAgentTeamByAgentId($id, $includeArchived = false, $sortByDate = 'ASC', $page = 1, $limit = self::DEFAULT_LIMIT)
	{
		$query = "
			SELECT s
			FROM DeskPRO:TwitterAccountStatus s
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
			FROM DeskPRO:TwitterAccountStatus s
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
		if ($page < 1) {
			$page = 1;
		}

		return ($page - 1) * $limit;
	}
}
