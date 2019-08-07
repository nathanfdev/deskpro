<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "submit community topic form" feature abuse.
 */
class SubmitCommunityTopicAbuseCheck extends AntiAbuseEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_SUBMIT_COMMUNITY_TOPIC;
    }
}
