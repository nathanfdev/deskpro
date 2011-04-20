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
	/**
	 * Returns an array indexed by department ID whose value is an array of
	 * categories enabled for it.
	 *
	 * @return array
	 */
	public function departmentToCategoryMap()
	{
		$map = App::getDb()->fetchAllGrouped("
			SELECT id, department_id
			FROM ticket_categories
		", array(), 'department_id', null, 'id');

		// If a parent category is used in a mapping, then it maps all subcats too
		foreach ($map as $depid => &$cats) {
			$add = array();
			foreach ($cats as $catid) {
				$subids = self::getIdsInTree($catid, false);
				if ($subids) {
					$add = array_merge($add, $subids);
				}
			}

			if ($add) {
				$cats = array_merge($cats, $add);
				array_unique($cats);
			}
		}
		unset($cats);

		// Further processing. Each category applied to a top level dep
		// means all sub-deps have the same settings
		foreach (App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy() as $dep) {
			if ($dep['children'] AND isset($map[$dep['id']])) {
				$dep_map = $map[$dep['id']];
				foreach ($dep['children'] as $subdep) {
					$subdep_map = $dep_map;
					if (isset($map[$subdep['id']])) {
						$subdep_map = array_merge($map[$subdep['id']], $subdep_map);
					}

					$map[$subdep['id']] = $subdep_map;
				}
			}
		}

		return $map;
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