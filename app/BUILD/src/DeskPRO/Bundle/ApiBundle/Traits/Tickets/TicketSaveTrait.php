<?php

namespace DeskPRO\Bundle\ApiBundle\Traits\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
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

        $person = $this->getPersonForExecutorContext($ticket, $options);
        if (($person && $person->isAgent()) || $eventMethod === ExecutorContext::METHOD_MOBILE || (isset($options['context']) && $options['context'] === 'agent')) {
            $context = $manager->createAgentExecutorContext($person, $event, $eventMethod, ['api_v2' => true]);
        } else {
            $context = $manager->createUserExecutorContext($person, $event, $eventMethod, ['api_v2' => true]);
        }

        if (isset($options['suppress_user_notify']) && $options['suppress_user_notify']) {
            $context->getVars()->set('mute_user_emails', true);
        }

        $manager->saveTicket($ticket, $context);
    }

    /**
     * @param Ticket $ticket
     * @param array  $options
     *
     * @return Person
     */
    protected function getPersonForExecutorContext(Ticket $ticket, $options)
    {
        $changes = $ticket->getStateChangeRecorder();

        if (
            $changes->hasTouchedField('person')
            && $ticket->getPerson()
            && !$ticket->getPerson()->isAgent()
        ) {
            return $ticket->getPerson();
        }

        if (isset($options['ticketAwareEntity']) && $options['ticketAwareEntity'] instanceof TicketMessage) {
            $message        = $options['ticketAwareEntity'];
            $messageChanges = $message->getStateChangeRecorder();

            if (
                $messageChanges->hasTouchedField('person')
                && $message->getPerson()
                && !$message->getPerson()->isAgent()
            ) {
                return $message->getPerson();
            }
        }

        return $this->getUser();
    }
}
