<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\HitRecord;

class HitRecordRepository extends AbstractEntityRepository
{
    /**
     * @param $visitorId
     *
     * @return HitRecord|null
     */
    public function findLastForVisitorId($visitorId)
    {
        return $this->createQueryBuilder('h')
            ->where('h.visitor_id = :vid')->setParameter('vid', $visitorId)
            ->orderBy('h.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
