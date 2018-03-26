<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PortalAgentRateLimit.
 */
class PortalAgentRateLimit
{
    /**
     * Settings for login.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup")
     *
     * @var RateLimitOptionsGroup
     */
    private $loginSettings;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->loginSettings = new RateLimitOptionsGroup();
    }

    /**
     * @return RateLimitOptionsGroup
     */
    public function getLoginSettings()
    {
        return $this->loginSettings;
    }
}
