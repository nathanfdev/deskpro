<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
