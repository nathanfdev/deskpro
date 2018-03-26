<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class Rating extends AbstractEntityRepository
{
    public function getRatingsFor($object_type, $object_id)
    {
        return $this->getEntityManager()->createQuery('
            SELECT r
            FROM DeskPRO:Rating r INDEX BY r.id
            LEFT JOIN r.person p
            WHERE r.object_type = ?1 AND r.object_id = ?2
            ORDER BY r.id
        ')->execute([1 => $object_type, 2 => $object_id]);
    }

    public function getRatingByPersonOnObject($object_type, $object_id, $person = null, $visitor_id = null)
    {
        if ($person) {
            return $this->getEntityManager()->createQuery('
                SELECT r
                FROM DeskPRO:Rating r INDEX BY r.id
                WHERE r.object_type = ?1 AND r.object_id = ?2 AND (r.person = ?3 OR r.visitor_id = ?4)
                ORDER BY r.id
            ')->setMaxResults(1)->setParameters([1 => $object_type, 2 => $object_id, 3 => $person, 4 => $visitor_id])->getOneOrNullResult();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT r
                FROM DeskPRO:Rating r INDEX BY r.id
                WHERE r.object_type = ?1 AND r.object_id = ?2 AND r.visitor_id = ?3
                ORDER BY r.id
            ')->setMaxResults(1)->setParameters([1 => $object_type, 2 => $object_id, 3 => $visitor_id])->getOneOrNullResult();
        }
    }
}
