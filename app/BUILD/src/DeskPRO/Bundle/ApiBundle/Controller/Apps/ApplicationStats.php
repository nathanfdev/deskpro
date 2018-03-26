<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use JMS\Serializer\Annotation as JMS;

class ApplicationStats
{

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("devInstanceCount")
     *
     * @var integer
     */
    private $app;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("devInstanceCount")
     *
     * @var integer
     */
    private $devInstanceCount;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("instanceCount")
     *
     * @var integer
     */
    private $instanceCount;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxInstances")
     *
     * @var int
     */
    private $maxInstances;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("totalInstances")
     *
     * @var int
     */
    private $totalInstances;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("installedInstances")
     *
     * @var int
     */
    private $installedInstances;


    /**
     * @param $count
     * @return $this
     */
    public function setDevInstanceCount($count)
    {
        $this->devInstanceCount = $count;
        return $this;
    }


    public function getDevInstanceCount()
    {
        return $this->devInstanceCount;
    }

    /**
     * @return int
     */
    public function getMaxInstances()
    {
        return $this->maxInstances;
    }

    /**
     * @param int $maxInstances
     */
    public function setMaxInstances( $maxInstances )
    {
        $this->maxInstances = $maxInstances;
    }

    /**
     * @return int
     */
    public function getTotalInstances()
    {
        return $this->totalInstances;
    }

    /**
     * @param int $totalInstances
     */
    public function setTotalInstances( $totalInstances )
    {
        $this->totalInstances = $totalInstances;
    }

    /**
     * @return int
     */
    public function getInstalledInstances()
    {
        return $this->installedInstances;
    }

    /**
     * @param int $totalInstances
     */
    public function setInstalledInstances( $totalInstances )
    {
        $this->totalInstances = $totalInstances;
    }
}
