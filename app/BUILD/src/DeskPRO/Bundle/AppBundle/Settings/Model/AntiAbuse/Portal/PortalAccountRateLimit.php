<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PortalAccountRateLimit.
 */
class PortalAccountRateLimit
{
    /**
     * Limits for registration.
     *
     * @var RateLimitLockoutGroup
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup")
     */
    private $registrationSettings;

    /**
     * Limits for password resetting.
     *
     * @Assert\Valid
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup")
     *
     * @var RateLimitLockoutGroup
     */
    private $resetPasswordSettings;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registrationSettings  = new RateLimitLockoutGroup();
        $this->resetPasswordSettings = new RateLimitLockoutGroup();
    }

    /**
     * @return RateLimitLockoutGroup
     */
    public function getRegistrationSettings()
    {
        return $this->registrationSettings;
    }

    /**
     * @return RateLimitLockoutGroup
     */
    public function getResetPasswordSettings()
    {
        return $this->resetPasswordSettings;
    }
}
