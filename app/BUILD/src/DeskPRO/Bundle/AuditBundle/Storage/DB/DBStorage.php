<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AuditBundle\Storage\DB;

use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractStorage;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class DBStorage extends AbstractStorage
{
    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->manager->getRepository(AuditLogEntity::class);
    }

    public function getPaginationAdapter($qb)
    {
        return new DoctrineORMAdapter($qb);
    }

    public function createQueryBuilder()
    {
        /** @var EntityManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder();
        $qb->select('e')->from(AuditLogEntity::class, 'e');

        return $qb;
    }

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
}
