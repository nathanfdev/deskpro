<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Sets the email validation flag for new users submitting tickets by email.
 *
 * @option bool enable_validation
 */
class ModSetEmailValidation extends AbstractAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getVars()->set('enable_email_validation', $this->getActionOption('enable_validation'));
    }
}
