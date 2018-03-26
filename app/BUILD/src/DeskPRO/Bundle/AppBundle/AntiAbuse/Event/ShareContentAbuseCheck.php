<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "share content" feature abuse.
 */
class ShareContentAbuseCheck extends AntiAbuseEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_SHARE_CONTENT;
    }
}
