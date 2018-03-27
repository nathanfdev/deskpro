<?php

namespace DeskPRO\Bundle\AuditBundle\Event;

use DeskPRO\Bundle\AuditBundle\Configuration\AuditContext;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreLogEvent.
 */
class LogEvent extends Event
{
    const PRE_LOG_EVENT    = 'audit_log.pre_log';
    const START_LOG_EVENT  = 'audit_log.start_log';
    const FINISH_LOG_EVENT = 'audit_log.finish_log';

    /**
     * @var bool
     */
    private $shouldLog = false;

    /**
     * @var bool
     */
    private $shouldWrite = true;

    /**
     * @var AuditContext
     */
    private $context;

    /**
     * @var AuditLog
     */
    private $log;

    /**
     * @var ClassMetadataInfo
     */
    private $metadata;

    /**
     * Constructor.
     *
     * @param AuditContext $context
     */
    public function __construct(AuditContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function shouldLog()
    {
        return $this->shouldLog;
    }

    /**
     * @param bool $shouldLog
     *
     * @return $this
     */
    public function setShouldLog($shouldLog)
    {
        $this->shouldLog = $shouldLog;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldWrite()
    {
        return $this->shouldWrite;
    }

    /**
     * @param mixed $shouldWrite
     *
     * @return $this
     */
    public function setShouldWrite($shouldWrite)
    {
        $this->shouldWrite = $shouldWrite;

        return $this;
    }

    /**
     * @return AuditContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return AuditLog
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param AuditLog $log
     *
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return ClassMetadataInfo
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }
}
