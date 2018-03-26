<?php

namespace DeskPRO\Bundle\AuditBundle\Storage;

use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use DeskPRO\Bundle\AuditBundle\Log\LoggableInterface;

/**
 * Interface TransformerInterface.
 */
interface TransformerInterface
{
    /**
     * @param AuditLog $log
     *
     * @return LoggableInterface
     */
    public function transform(AuditLog $log);

    /**
     * @param LoggableInterface $loggable
     *
     * @return AuditLog
     */
    public function reverseTransform(LoggableInterface $loggable);

    /**
     * @param LoggableInterface[] $collection
     *
     * @return AuditLog
     */
    public function reverseTransformCollection(array $collection);

    /**
     * @param AuditLog          $log
     * @param LoggableInterface $loggable
     *
     * @return LoggableInterface
     */
    public function updateLog(LoggableInterface $loggable, AuditLog $log);
}
