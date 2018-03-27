<?php

namespace DeskPRO\Bundle\ImportBundle\Serializer\Model;

use Application\DeskPRO\Entity\Job;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ImportStatus.
 */
class ImportStatus
{
    /**
     * The unique ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sourceType;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @JMS\Type("array<string>")
     *
     * @var \string[]
     */
    private $importedSteps;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $importedCounts;

    /**
     * @JMS\Type("array<string>")
     *
     * @var \string[]
     */
    private $appliedSteps;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $appliedCounts;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $log;

    /**
     * Constructor.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->id             = $job->getId();
        $this->sourceType     = $job->getDataKey('type');
        $this->status         = $job->getStatus();
        $this->importedSteps  = $job->getDataKey('imported_steps', []);
        $this->importedCounts = $job->getDataKey('imported_counts', []);
        $this->appliedSteps   = $job->getDataKey('applied_steps', []);
        $this->appliedCounts  = $job->getDataKey('applied_counts', []);
        $this->dateCreated    = $job->getDateCreated();
        $this->log            = $job->getLog();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @return \string[]
     */
    public function getImportedSteps()
    {
        return $this->importedSteps;
    }

    /**
     * @return array
     */
    public function getImportedCounts()
    {
        return $this->importedCounts;
    }

    /**
     * @return \string[]
     */
    public function getAppliedSteps()
    {
        return $this->appliedSteps;
    }

    /**
     * @return array
     */
    public function getAppliedCounts()
    {
        return $this->appliedCounts;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }
}
