<?php

namespace DeskPRO\Bundle\AppBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;

/**
 * Adds a protocol to a URL if it doesn't already have one.
 */
class FixUrlProtocolListener extends \Symfony\Component\Form\Extension\Core\EventListener\FixUrlProtocolListener
{
    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (preg_match('#^\\\\[\w\d-_\\\]+$#', $data)) {
            // shared folder
            return;
        }
        if (!preg_match('/\.+/', $data)) {
            return;
        }

        parent::onSubmit($event);
    }
}
