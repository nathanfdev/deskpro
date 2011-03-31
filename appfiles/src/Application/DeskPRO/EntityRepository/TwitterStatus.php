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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Numbers;

class TwitterStatus extends EntityRepository
{
	protected function normalizeSortByDate($sortByDate = 'asc')
	{
		// check that sort by date is asc or desc
		if (!in_array(strtolower($sortByDate), array('asc', 'desc'))) {
			$sortByDate = 'asc';
		}

		return strtoupper($sortByDate);
	}

	/**
	 * @param array $userIds An array of TwitterUser ids
	 * @param Boolean $includeArchived (optional)
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findByUserIds(array $userIds, $includeArchived = false, $sortByDate = 'ASC')
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

		$query .= sprintf("ORDER BY s.date_created %s", $this->normalizeSortByDate($sortByDate));

		$statuses = $this->getEntityManager()->createQuery($query)->execute();

		return $statuses;
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findMessagesForUserId($id, $sortByDate = 'ASC')
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
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findRepliesForUserId($id, $sortByDate = 'ASC')
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
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findMentionsForUserId($id, $sortByDate = 'ASC')
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
			->execute(array(
				'user_id' => $id
			));
	}

	/**
	 * @todo implement
	 */
	public function findRetweetsForUserId($id, $sortByDate = 'ASC')
	{
	 	return array();
	}

	/**
	 * @param integer $id
	 * @param string $sortByDate (optional)
	 * @return array
	 */
	public function findOutgoingByUserId($id, $sortByDate = 'ASC')
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
			->execute(array(
				'user_id' => $id
			));
	}
}
