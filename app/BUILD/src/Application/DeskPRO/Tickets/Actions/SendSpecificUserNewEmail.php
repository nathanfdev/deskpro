<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Send an email to the user.
 *
 * @option bool     template          The template to send
 * @option bool     from_name         Who to send the email from
 * @option bool     from_account      The account to send from (falsey for ticket account)
 * @option string[] emails            Email addresses to send to
 * @option bool     send_org_managers True to send to all org managers
 */
class SendSpecificUserNewEmail extends SendArbitraryUserNewEmail
{
    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        return false;
    }
}
