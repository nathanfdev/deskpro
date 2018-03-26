<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds agents to the subscription list, effectively forcing their notification
 * preference to be on for the current trigger run.
 */
class ModForceAgentEmails extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('agent_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $force_list = $context->getVars()->get('agent_force_subscription_list', []);

        $agent_ids = $this->getActionOption('agent_ids', []);
        if (!is_array($agent_ids)) {
            $agent_ids = [$agent_ids];
        }

        $agent_data     = $this->getContainer()->getAgentData();
        $person_context = $context->getPersonContext();

        foreach ($agent_ids as $aid) {
            $force_list = array_merge($force_list, $agent_data->selectAgents($aid, $person_context, $ticket));
        }

        $force_list = array_unique($force_list);

        $ids = array_map(function ($a) {
            return $a->id;
        }, $force_list);
        $context->getLogger()->info('[ModForceAgents] Force list: '.implode(', ', $ids));

        $context->getVars()->set('agent_force_subscription_list', $force_list);
    }
}
