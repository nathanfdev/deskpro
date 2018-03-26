<?php

namespace DeskPRO\Bundle\AuditBundle\Storage\DB;

use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractStorage;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * Class DBStorage.
 */
class DBStorage extends AbstractStorage
{
    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->manager->getRepository(AuditLogEntity::class);
    }

    /**
     * @param $qb
     *
     * @return DoctrineORMAdapter
     */
    public function getPaginationAdapter($qb)
    {
        return new DoctrineORMAdapter($qb);
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        /** @var EntityManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder();
        $qb->select('e')->from(AuditLogEntity::class, 'e')->orderBy('e.id', 'DESC');

        return $qb;
    }

    /**
     * @param $filters
     * @param $qb
     *
     * @return QueryBuilder
     */
    public function applyFilters($filters, $qb)
    {

        /* @var QueryBuilder $qb */
        if (isset($filters['date_created_from'])) {
            $qb->andWhere($qb->expr()->gte('e.dateCreated', ':date_created_from'));
            $qb->setParameter('date_created_from', $filters['date_created_from']);
            unset($filters['date_created_from']);
        }
        if (isset($filters['date_created_to'])) {
            $qb->andWhere($qb->expr()->lte('e.dateCreated', ':date_created_to'));
            $qb->setParameter('date_created_to', $filters['date_created_to']);
            unset($filters['date_created_to']);
        }

        foreach ($filters as $name => $value) {
            $name = StringUtils::toCamelCase($name, false);
            $qb->andWhere($qb->expr()->eq("e.$name", ":{$name}"))->setParameter($name, $value);
        }

        return $qb;
    }

    public function deleteByPeriod($period)
    {
        /** @var EntityManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder();
        $qb
            ->delete(AuditLogEntity::class, 'al')
            ->where('al.dateCreated < :date_created')
            ->setParameter('date_created', $this->getDate($period))
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteAll()
    {
        /** @var EntityManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder();
        $qb->delete(AuditLogEntity::class)->getQuery()->execute();
    }
}
