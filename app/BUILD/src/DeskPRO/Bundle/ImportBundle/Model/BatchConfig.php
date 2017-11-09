<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
