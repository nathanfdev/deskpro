<?php

/**
 * Orb.
 */

namespace Orb\Mail\Plugins;

/**
 * Overrides the 'to' of a message, good for debugging.
 */
class ForceToAddress implements \Swift_Events_SendListener
{
    /** @var mixed */
    protected $to;

    public function __construct($new_to)
    {
        $this->to = $new_to;
    }

    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();

        $message->setTo($this->to);
        $headers->removeAll('Cc');
        $headers->removeAll('Bcc');
    }
}
