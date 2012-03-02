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

class TicketPriority extends EntityRepository
{
	protected $priority_names = null;

	public function findByTitle($title)
	{
		try {
			$priority = $this->getEntityManager()->createQuery("
				SELECT p
				FROM DeskPRO:TicketPriority p
				WHERE p.title LIKE ?1
			")->setParameter(1, "%$title%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $priority;
	}

	/**
	 * @return array
	 */
	public function getPriorityNames($for_ids = null)
	{
		if ($this->priority_names !== null && !$for_ids) return $this->priority_names;

		if (!$this->priority_names && ($this->priority_names = App::getCache('common')->load('priority_names')) === false) {

			$db = App::getDb();
			$this->priority_names = $db->fetchAllKeyValue("
				SELECT id, title
				FROM ticket_priorities
				ORDER BY priority ASC
			");

			App::getCache('common')->save($this->priority_names, null, array('ticket_priorities'));
		}

		if ($for_ids) {
			$ret = array();
			foreach ($for_ids as $id) {
				$ret[$id] = $this->priority_names[$id];
			}
			return $ret;
		}

		return $this->priority_names;
	}



	/**
	 * Get all priority IDs in the order they are meant to go
	 *
	 * @return array
	 */
	public function getIdsInOrder()
	{
		$names = $this->getPriorityNames();
		return array_keys($names);
	}


	/**
	 * Count all pris that exist
	 *
	 * @return int
	 */
	public function countAll()
	{
		return App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_priorities");
	}



	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('ticket_priorities'));
	}

	/**
	 * @see \Application\DeskPRO\DBAL\Logging\CacheInvalidor
	 * @param  $sql
	 * @return void
	 */
	public function invalidateFromQuery($sql)
	{
		$this->invalidateCaches();
	}
}
