<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Event;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;

/**
 * Fire this with the AntiAbuse service to log a file upload attempt and
 * get recommendations about what to do if abuse is detected.
 *
 * NOTE: if you are checking for LOCKOUT, then a person object IS required
 *       in otherwords, LOCKOUT functionality is only checked if the email
 *       the user provides is actually a Person.
 */
class UploadAbuseCheck extends AntiAbuseEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return AntiAbuse::ACTION_UPLOAD;
    }
}
