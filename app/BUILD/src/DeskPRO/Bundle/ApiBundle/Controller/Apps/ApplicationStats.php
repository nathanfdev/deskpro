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
