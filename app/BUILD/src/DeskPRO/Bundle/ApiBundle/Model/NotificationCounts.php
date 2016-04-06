<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class NotificationCounts.
 */
class NotificationCounts
{
    /**
     * Count of dismissed notifications.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $dismissed;

    /**
     * Count of non dismissed notifications.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $nonDismissed;

    /**
     * Constructor.
     *
     * @param int $nonDismissed
     * @param int $dismissed
     */
    public function __construct($nonDismissed, $dismissed)
    {
        $this->dismissed    = $dismissed;
        $this->nonDismissed = $nonDismissed;
    }

    /**
     * @return int
     */
    public function getDismissed()
    {
        return $this->dismissed;
    }

    /**
     * @return int
     */
    public function getNonDismissed()
    {
        return $this->nonDismissed;
    }

    /**
     * Total notifications count.
     *
     * @return int
     *
     * @JMS\Type("integer")
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("total")
     */
    public function getTotal()
    {
        return $this->dismissed + $this->nonDismissed;
    }
}
