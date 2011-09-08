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
	 * @return array
	 */
	public function getByIds(array $ids)
	{
		if (!$ids) return array();

		$name = $this->_entityName;
		$id = $this->_class->identifier[0];

		return $this->getEntityManager()->createQuery("
			SELECT o
			FROM {$name} o
			WHERE o.{$id} IN (?1)
		")->execute(array(1=> $ids));
	}
}
