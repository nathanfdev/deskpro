<?php
namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

class ActionVars
{
    /**
     * Makes the required context vars available as formatter vars
     *
     * @param SnippetFormatter $formatter
     * @param ExecutorContextInterface $context
     * @return null
     */
    static public function configureFormatter(SnippetFormatter $formatter, ExecutorContextInterface $context)
    {
        $formatter->addVar('user_vars', $context->getUserVars());

        $webhookVars = TicketWebhookVars\ExecutorContextEnv::getTriggerVars($context);
        foreach ($webhookVars as $name => $value) {
            $formatter->addVar($name, $value);
        }
    }
}
