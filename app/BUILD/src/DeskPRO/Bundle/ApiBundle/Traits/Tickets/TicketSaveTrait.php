<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Traits\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;

/**
 * Class SaveTicketTrait.
 *
 * @method DeskproContainer getContainer()
 * @method Person           getUser()
 */
trait TicketSaveTrait
{
    /**
     * Save the ticket via ticket manager with proper context.
     *
     * @param Ticket $ticket
     * @param array  $options
     */
    protected function saveTicket(Ticket $ticket, array $options = [])
    {
        $manager = $this->getContainer()->getTicketManager();
        $changes = $ticket->getStateChangeRecorder();

        if ($changes->isNewTicket()) {
            $event = ExecutorContext::EVENT_NEW;
        } elseif ($changes->hasNewReply()) {
            $event = ExecutorContext::EVENT_REPLY;
        } elseif ($changes->isDeleted()) {
            $event = ExecutorContext::EVENT_DELETE;
        } else {
            $event = ExecutorContext::EVENT_UPDATE;
        }

        $eventMethod = ExecutorContext::METHOD_API;
        if ($this->getContainer()->get('api_client_info')->isMobileClient()) {
            $eventMethod = ExecutorContext::METHOD_MOBILE;
        }

        if (
            $changes->hasTouchedField('person')
            && $ticket->getPerson()
            && !$ticket->getPerson()->isAgent()
            && in_array($event, [
                ExecutorContext::EVENT_NEW,
                ExecutorContext::EVENT_REPLY,
                ExecutorContext::EVENT_UPDATE, ])
        ) {
            $context = $manager->createUserExecutorContext($ticket->getPerson(), $event, $eventMethod);
        } else {
            $context = $manager->createAgentExecutorContext($this->getUser(), $event, $eventMethod);
        }

        if (isset($options['suppress_user_notify']) && $options['suppress_user_notify']) {
            $context->getVars()->set('mute_user_emails', true);
        }

        $manager->saveTicket($ticket, $context);
    }
}
