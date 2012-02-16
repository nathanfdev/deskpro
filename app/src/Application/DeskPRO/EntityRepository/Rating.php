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

use Application\DeskPRO\App;
use Orb\Util\Arrays;

class Rating extends \Doctrine\ORM\EntityRepository
{
	public function getRatingsFor($object_type, $object_id)
	{
		return $this->getEntityManager()->createQuery("
			SELECT r
			FROM DeskPRO:Rating r INDEX BY r.id
			LEFT JOIN r.person p
			WHERE r.object_type = ?1 AND r.object_id = ?2
			ORDER BY r.id
		")->execute(array(1=> $object_type, 2=> $object_id));
	}

	public function getRatingByPersonOnObject($object_type, $object_id, $person = null, $visitor = null)
	{
		if ($person) {
			return $this->getEntityManager()->createQuery("
				SELECT r
				FROM DeskPRO:Rating r INDEX BY r.id
				WHERE r.object_type = ?1 AND r.object_id = ?2 AND (r.person = ?3 OR r.visitor = ?4)
				ORDER BY r.id
			")->setParameters(array(1=> $object_type, 2=> $object_id, 3=> $person, 4=> $visitor))->getOneOrNullResult();
		} else {
			return $this->getEntityManager()->createQuery("
				SELECT r
				FROM DeskPRO:Rating r INDEX BY r.id
				WHERE r.object_type = ?1 AND r.object_id = ?2 AND r.visitor = ?3
				ORDER BY r.id
			")->setParameters(array(1=> $object_type, 2=> $object_id, 3=> $visitor))->getOneOrNullResult();
		}
	}
}
