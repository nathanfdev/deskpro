<?php

namespace DeskPRO\Bundle\AuditBundle\Log;

use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use DeskPRO\Bundle\AuditBundle\Storage\StorageInterface;
use DeskPRO\Bundle\AuditBundle\Storage\TransformerInterface;

/**
 * Class AuditLogService.
 */
class AuditLogService
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @var AuditLogHelper
     */
    private $helper;

    const PERIOD_1_DAY    = '1 DAY';
    const PERIOD_1_WEEK   = '7 DAYS';
    const PERIOD_1_MONTH  = '30 DAYS';
    const PERIOD_3_MONTHS = '3 MONTHS';
    const PERIOD_6_MONTHS = '6 MONTHS';
    const PERIOD_1_YEAR   = '1 YEAR';

    /**
     * AuditLogService constructor.
     *
     * @param StorageInterface     $storage
     * @param TransformerInterface $transformer
     * @param AuditLogHelper       $helper
     */
    public function __construct(StorageInterface $storage, TransformerInterface $transformer, AuditLogHelper $helper)
    {
        $this->storage     = $storage;
        $this->transformer = $transformer;
        $this->helper      = $helper;
    }

    /**
     * @param AuditLog $log
     *
     * @return AuditLogEntity
     */
    public function write(AuditLog $log)
    {
        if ($log->getId()) {
            $concreteLog = $this->storage->find($log->getId());
            $concreteLog = $this->transformer->updateLog($concreteLog, $log);
        } else {
            $concreteLog = $this->transformer->transform($log);
        }
        $this->storage->write($concreteLog);
        $log->setId($concreteLog->getId());

        return $concreteLog;
    }

    /**
     * @param $id
     *
     * @return AuditLog
     */
    public function getLog($id)
    {
        $concreteLog = $this->storage->find($id);

        return $this->transformer->reverseTransform($concreteLog);
    }

    /**
     * @param $offset
     * @param $limit
     *
     * @return AuditLog
     */
    public function read($offset, $limit)
    {
        $concreteLogs = $this->storage->read($offset, $limit);

        return $this->transformer->reverseTransformCollection($concreteLogs);
    }

    /**
     * @param null $period
     */
    public function delete($period = null)
    {
        if ($period) {
            $this->storage->deleteByPeriod($period);
        } else {
            $this->storage->deleteAll();
        }

        return;
    }
}
