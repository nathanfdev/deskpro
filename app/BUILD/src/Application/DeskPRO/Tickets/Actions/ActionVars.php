<?php
namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

class ActionVars
{

    static public function getContextVars(ExecutorContextInterface $context)
    {
        return array_merge(
            TicketWebhookVars\ExecutorContextEnv::getTriggerVars($context),
            [
                'user_vars', $context->getUserVars()
            ]
        );
    }

    /**
     * Makes the required context vars available as formatter vars
     *
     * @param SnippetFormatter $formatter
     * @param ExecutorContextInterface $context
     * @return null
     */
    static public function configureFormatter(SnippetFormatter $formatter, ExecutorContextInterface $context)
    {
        $vars = ActionVars::getContextVars($context);
        foreach ($vars as $name => $value) {
            $formatter->addVar($name, $value);
        }
    }
}
