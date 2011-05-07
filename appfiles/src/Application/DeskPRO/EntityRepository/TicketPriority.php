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
	public function getPriorityNames()
	{
		if ($this->priority_names !== null) return $this->priority_names;

		if (($this->priority_names = App::getCache('common')->load('priority_names')) === false) {

			$db = App::getDb();
			$this->priority_names = $db->fetchAllKeyValue("
				SELECT id, title
				FROM ticket_priorities
				ORDER BY priority ASC
			");

			App::getCache('common')->save($this->priority_names, null, array('ticket_priorities'));
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