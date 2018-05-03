<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "forgot password" feature abuse.
 */
class PasswordResetAbuseCheck extends AntiAbuseLockoutEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_RESET_PASSWORD;
    }
}
