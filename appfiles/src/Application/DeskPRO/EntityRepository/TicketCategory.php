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
use \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;

use \Orb\Util\Arrays;

class TicketCategory extends AbstractCategoryRepository
{
	public function findByTitle($title)
	{
		try {
			$category = $this->getEntityManager()->createQuery("
				SELECT c
				FROM DeskPRO:TicketCategory c
				WHERE c.title LIKE ?1
			")->setParameter(1, "%$title%")->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}

		return $category;
	}
	
	/**
	 * Count all cats that exist
	 *
	 * @return int
	 */
	public function countAll()
	{
		return App::getDb()->fetchColumn("SELECT COUNT(*) FROM ticket_categories");
	}

	/**
	 * Invalidates caches
	 */
	public function invalidateCaches()
	{
		App::getCache('common')->clean('matchingTag', array('ticket_categories'));
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