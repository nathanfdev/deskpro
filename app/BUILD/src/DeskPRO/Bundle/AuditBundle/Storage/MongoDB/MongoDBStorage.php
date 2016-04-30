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

namespace DeskPRO\Bundle\AuditBundle\Storage\MongoDB;

use DeskPRO\Bundle\AuditBundle\Document\AuditLog as AuditLogDocument;
use DeskPRO\Bundle\AuditBundle\Storage\AbstractStorage;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;

class MongoDBStorage extends AbstractStorage
{
    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->manager->getRepository(AuditLogDocument::class);
    }

    public function getPaginationAdapter($qb)
    {
        return new DoctrineODMMongoDBAdapter($qb);
    }

    public function createQueryBuilder()
    {
        /** @var DocumentManager $manager */
        $manager = $this->manager;

        return $manager->createQueryBuilder(AuditLogDocument::class);
    }

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
}
