<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "registration form" feature abuse.
 */
class TokenExchangeAbuseCheck extends AntiAbuseEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_TOKEN_EXCHANGE;
    }
}
