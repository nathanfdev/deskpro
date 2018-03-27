<?php

namespace DeskPRO\Bundle\AuditBundle\Storage\MongoDB;

use DeskPRO\Bundle\AuditBundle\Document\AuditLog as AuditLogDocument;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractStorage;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;

/**
 * Class MongoDBStorage.
 */
class MongoDBStorage extends AbstractStorage
{
    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->manager->getRepository(AuditLogDocument::class);
    }

    /**
     * @param $qb
     *
     * @return DoctrineODMMongoDBAdapter
     */
    public function getPaginationAdapter($qb)
    {
        return new DoctrineODMMongoDBAdapter($qb);
    }

    /**
     * @return Builder
     */
    public function createQueryBuilder()
    {
        /** @var DocumentManager $manager */
        $manager = $this->manager;

        return $manager->createQueryBuilder(AuditLogDocument::class);
    }

    /**
     * @param $filters
     * @param $qb
     *
     * @return Builder
     */
    public function applyFilters($filters, $qb)
    {
        /* @var Builder $qb */

        if (isset($filters['date_created_from'])) {
            try {
                $qb->field('dateCreated')->gte(new \DateTime($filters['date_created_from']));
            } finally {
                unset($filters['date_created_from']);
            }
        }
        if (isset($filters['date_created_to'])) {
            try {
                $qb->field('dateCreated')->lte(new \DateTime($filters['date_created_to']));
            } finally {
                unset($filters['date_created_to']);
            }
        }

        $fieldTypes = $this->getFieldTypes();

        foreach ($filters as $name => $value) {
            $name = StringUtils::toCamelCase($name, false);
            if (in_array($name, array_keys($fieldTypes))) {
                $value = (int) $value;
            }
            $qb->field($name)->equals($value);
        }

        return $qb;
    }

    /**
     * @todo should be done better of course
     *
     * @return array
     */
    private function getFieldTypes()
    {
        return [
            'objectId'    => 'integer',
            'performerId' => 'integer',
            'apiKey'      => 'integer',
        ];
    }

    public function deleteByPeriod($period)
    {
        /** @var DocumentManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder(AuditLogDocument::class);

        $qb->field('dateCreated');

        $date = $this->getDate($period);
        if ($date) {
            $qb->lt($date);
        }
        $qb->remove()->getQuery()->execute();
    }

    public function deleteAll()
    {
        /** @var DocumentManager $manager */
        $manager = $this->manager;
        $qb      = $manager->createQueryBuilder(AuditLogDocument::class);
        $qb->remove()->getQuery()->execute();
    }
}
