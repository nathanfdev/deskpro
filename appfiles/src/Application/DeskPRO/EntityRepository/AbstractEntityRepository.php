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

use Orb\Util\Arrays;
use Orb\Util\Strings;

class AbstractEntityRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Get a collection of entities by ID
	 *
	 * @param bool $keep_order True to order the resulting array in the same order that ids are provided in $ids
	 * @return array
	 */
	public function getByIds(array $ids, $keep_order = false)
	{
		if (!$ids) return array();

		$name = $this->_entityName;
		$id = $this->_class->identifier[0];

		$results = $this->getEntityManager()->createQuery("
			SELECT o
			FROM {$name} o INDEX BY o.{$id}
			WHERE o.{$id} IN (?1)
		")->execute(array(1=> $ids));

		if ($keep_order) {
			$results = Arrays::orderIdArray($ids, $results, true);
		}

		return $results;
	}
}
