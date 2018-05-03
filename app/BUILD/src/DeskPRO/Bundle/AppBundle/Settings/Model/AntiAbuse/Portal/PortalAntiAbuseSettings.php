<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PortalAntiAbuseSettings.
 */
class PortalAntiAbuseSettings
{
    /**
     * Account rate limit object.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAccountRateLimit")
     *
     * @var PortalAccountRateLimit
     */
    private $accountRateLimit;

    /**
     * Agent rate limit object.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAgentRateLimit")
     *
     * @var PortalAgentRateLimit
     */
    private $agentRateLimit;

    /**
     * Rate limits for user.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit")
     *
     * @var PortalUserRateLimit
     */
    private $userRateLimit;

    /**
     * Rate limits for guests.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit")
     *
     * @var PortalUserRateLimit
     */
    private $guestRateLimit;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accountRateLimit = new PortalAccountRateLimit();
        $this->userRateLimit    = new PortalUserRateLimit();
        $this->agentRateLimit   = new PortalAgentRateLimit();
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
     * @return PortalAgentRateLimit
     */
    public function getAgentRateLimit()
    {
        return $this->agentRateLimit;
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
