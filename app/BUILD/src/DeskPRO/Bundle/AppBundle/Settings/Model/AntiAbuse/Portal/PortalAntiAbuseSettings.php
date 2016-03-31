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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PortalAntiAbuseSettings.
 */
class PortalAntiAbuseSettings
{
    /**
     * @var PortalAccountRateLimit
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAccountRateLimit")
     */
    private $accountRateLimit;

    /**
     * @var PortalUserRateLimit
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit")
     */
    private $userRateLimit;

    /**
     * @var PortalUserRateLimit
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit")
     */
    private $guestRateLimit;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accountRateLimit = new PortalAccountRateLimit();
        $this->userRateLimit    = new PortalUserRateLimit();
        $this->guestRateLimit   = new PortalUserRateLimit();
    }

    /**
     * @return PortalAccountRateLimit
     */
    public function getAccountRateLimit()
    {
        return $this->accountRateLimit;
    }

    /**
     * @return PortalUserRateLimit
     */
    public function getUserRateLimit()
    {
        return $this->userRateLimit;
    }

    /**
     * @return PortalUserRateLimit
     */
    public function getGuestRateLimit()
    {
        return $this->guestRateLimit;
    }
}
