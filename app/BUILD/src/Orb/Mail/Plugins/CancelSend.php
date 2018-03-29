<?php

/**
 * Orb.
 */

namespace Orb\Mail\Plugins;

/**
 * Completely turns off email sending.
 */
class CancelSend implements \Swift_Events_SendListener
{
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $evt->cancelBubble();
    }
}
