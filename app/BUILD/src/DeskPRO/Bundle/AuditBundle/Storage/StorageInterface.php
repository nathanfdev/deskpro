<?php

namespace DeskPRO\Bundle\AuditBundle\Storage;

use DeskPRO\Bundle\AuditBundle\Log\LoggableInterface;

/**
 * Interface StorageInterface.
 */
interface StorageInterface
{
    /**
     * @param LoggableInterface $log
     *
     * @return mixed
     */
    public function write(LoggableInterface $log);

    public function finishWriting();

    /**
     * @param mixed $id
     *
     * @return LoggableInterface
     */
    public function find($id);

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return LoggableInterface[]
     */
    public function read($offset, $limit);

    public function getPaginationAdapter($qb);

    public function createQueryBuilder();

    public function applyFilters($filters, $qb);

    public function deleteByPeriod($period);

    public function deleteAll();
}
