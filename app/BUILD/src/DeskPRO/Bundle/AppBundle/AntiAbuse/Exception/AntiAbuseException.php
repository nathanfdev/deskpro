<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\Exception;

use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;

/**
 * This is fired by the AntiAbuse system when we need to return a response
 * to the user. The various kernels are configured to handle an AntiAbuseException in different ways.
 *
 * Usually, it is fired when an AntiAbuseEvent has a response that must
 * be returned to the user immediately (Account Locked Out for example).
 */
class AntiAbuseException extends \RuntimeException
{
    protected $event;

    public function __construct(AntiAbuseEvent $event)
    {
        parent::__construct();

        $this->event = $event;
    }

    public function getAntiAbuseEvent()
    {
        return $this->event;
    }
}
