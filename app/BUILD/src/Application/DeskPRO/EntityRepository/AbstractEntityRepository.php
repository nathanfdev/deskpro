<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

class AbstractEntityRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @var \Application\DeskPRO\EntityRepository\Helper\IdentityHelper
     */
    protected $identity_helper;

    /**
     * @return \Application\DeskPRO\EntityRepository\Helper\IdentityHelper
     */
    public function getIdentityHelper()
    {
        if (!$this->identity_helper) {
            $this->identity_helper = new Helper\IdentityHelper($this->getEntityManager(), $this);
        }

        return $this->identity_helper;
    }

    /**
     * Get a collection of entities by ID.
     *
     * @param array $ids
     * @param bool  $keep_order True to order the resulting array in the same order that ids are provided in $ids
     *
     * @return array
     */
    public function getByIds(array $ids, $keep_order = false)
    {
        if (!$ids) {
            return [];
        }

        $class = $this->getName();

        $ids = array_values($ids);

        $q_res = $this->getEntityManager()->createQuery("
                SELECT o
                FROM {$class} o INDEX BY o.id
                WHERE o.id IN(?0)
            ")->execute([$ids]);

        if ($keep_order) {
            $q_res = Arrays::orderIdArray($ids, $q_res);
        }

        return $q_res;
    }

    /**
     * @return array
     */
    public function getAllIndexedById()
    {
        $class = $this->getName();

        return $this->getEntityManager()->createQuery("
            SELECT o
            FROM {$class} o INDEX BY o.id
        ")->execute();
    }

    /**
     * Alias for find.
     *
     * @param int $id
     *
     * @return object
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * @return int
     */
    public function countAll()
    {
        return $this->getEntityManager()->getConnection()->count($this->getTableName());
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getClassMetadata()->getTableName();
    }

    public function getFieldMappings()
    {
        return $this->getClassMetadata()->fieldMappings;
    }

    public function getAssociationMappings()
    {
        return $this->getClassMetadata()->getAssociationMappings();
    }

    public function getReportAssociations()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getClassMetadata()->getName();
    }

    public function createSearchQueryBuilder($entityAlias)
    {
        return $this->createQueryBuilder($entityAlias);
    }
}
