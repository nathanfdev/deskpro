<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Json batch configuration.
 *
 * Class BatchConfig
 */
class BatchConfig
{
    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateModified;

    /**
     * @var int[]
     *
     * @JMS\Type("array")
     */
    protected $batchIds = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param \DateTime $dateModified
     *
     * @return $this
     */
    public function setDateModified(\DateTime $dateModified = null)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getBatchIds()
    {
        return $this->batchIds;
    }

    /**
     * @param string $type
     *
     * @return int|null
     */
    public function getBatchId($type)
    {
        return isset($this->batchIds[$type]) ? $this->batchIds[$type] : null;
    }

    /**
     * @param string $type
     * @param int    $batchNum
     *
     * @return $this
     */
    public function setBatchId($type, $batchNum)
    {
        $this->batchIds[$type] = $batchNum;

        return $this;
    }

    /**
     * @param int[] $batchIds
     *
     * @return $this
     */
    public function setBatchIds($batchIds)
    {
        $this->batchIds = $batchIds;

        return $this;
    }
}
