<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Generator mapper interface.
 *
 * Interface MapperInterface
 */
interface MapperInterface
{
    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * Returns the DeskPRO record by criteria.
     *
     * @param array $criteria
     *
     * @return mixed
     */
    public function findOneBy(array $criteria);

    /**
     * @param int $id
     *
     * @return null|object
     */
    public function find($id);
}
