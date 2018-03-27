<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TicketDisableAutoProcessListener.
 */
class TicketDisableAutoProcessListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onDisableAutoProcess',
        ];
    }

    /**
     * Disable auto process before submitting form.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onDisableAutoProcess(FormEvent $event)
    {
        $data = $event->getForm()->getData();

        if ($data instanceof Ticket) {
            $ticket = $data;
        } elseif (is_object($data) && method_exists($data, 'getTicket')) {
            $ticket = $data->getTicket();
        } else {
            $ticket = null;
        }

        if ($ticket instanceof Ticket) {
            $ticket->disableAutoTicketProcess();
        }
    }
}
