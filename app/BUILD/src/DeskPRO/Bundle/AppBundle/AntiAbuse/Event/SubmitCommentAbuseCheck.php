<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Check vs "registration form" feature abuse.
 */
class SubmitCommentAbuseCheck extends AntiAbuseEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_SUBMIT_COMMENT;
    }
}
