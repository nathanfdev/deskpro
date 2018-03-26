<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "registration form" feature abuse.
 */
class RegistrationAbuseCheck extends AntiAbuseLockoutEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_REGISTER;
    }
}
