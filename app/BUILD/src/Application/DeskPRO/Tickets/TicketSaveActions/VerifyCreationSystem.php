<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class VerifyCreationSystem implements TicketSaveActionInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$ticket->creation_system) {
            if ($context->getEventMethod() == 'email') {
                $creation_system = 'gateway.';

                if ($context->getEventPerformer() == 'agent') {
                    $creation_system .= 'agent';
                } else {
                    $creation_system .= 'person';
                }
            } elseif ($context->getEventMethod() == 'mobile') {
                $creation_system = 'mobile.';

                if ($context->getEventPerformer() == 'agent') {
                    $creation_system .= 'agent';
                } else {
                    $creation_system .= 'person';
                }
            } elseif ($context->getEventMethod() == 'api') {
                $creation_system = 'web.api.';

                if ($context->getEventPerformer() == 'agent') {
                    $creation_system .= 'agent';
                } else {
                    $creation_system .= 'person';
                }
            } else {
                $creation_system = 'web.';

                if ($context->getEventPerformer() == 'agent') {
                    $creation_system .= 'agent.portal';
                } else {
                    if ($context->getEventMethodOption('is_widget')) {
                        $creation_system .= 'person.widget';
                    } elseif ($context->getEventMethodOption('is_embedded')) {
                        $creation_system .= 'person.embed';
                    } else {
                        $creation_system .= 'person.portal';
                    }
                }
            }

            $ticket->creation_system = $creation_system;

            if ($context->getEventMethodOption('origin_url')) {
                $ticket->creation_system_option = $context->getEventMethodOption('origin_url');
            }
        }
    }
}
