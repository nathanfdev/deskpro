<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFilterSubscription;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Notifications\AgentNotifyListBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Sets a flag for a ticket. In a trigger context, this sets on every agent account.
 * In a macro context, only sets the flag on the current agent.
 *
 * @option string color
 */
class SetFlag extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('color');
        $options->addValidNames('agent_ids');

        return $options;
    }

    /**
     * @param Connection $db
     * @param Ticket     $ticket
     * @param Person     $person
     * @param string     $color
     */
    private function saveFlag(Connection $db, $ticket, $person, $color)
    {
        $color ? $db->replace('tickets_flagged', [
                    'person_id' => $person->id,
                    'ticket_id' => $ticket->id,
                    'color'     => $color,
                ])
                : $db->delete('tickets_flagged', [
                    'person_id' => $person->id,
                    'ticket_id' => $ticket->id,
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $agents   = [];
        $agentIds = $this->getActionOption('agent_ids', ['all_agents']);

        foreach ($agentIds as $aid) {
            if ('all_agents' === $aid) {
                $context->getLogger()->debug('[SetFlag] notify_list all agents');
                $agents = $this->getContainer()->getAgentData()->getAgents();
                break;
            }

            if ($aid == 'notify_list') {
                $context->getLogger()->debug('[SetFlag] notify_list using notify_list');
                $isNotifDisabled = $this->getContainer()->getSetting('agent.disable_notifications');
                if ($isNotifDisabled) {
                    continue;
                }

                /** @var \Application\DeskPRO\EntityRepository\TicketFilterSubscription $subscriptionRepo */
                $subscriptionRepo = $this->getContainer()->getEm()->getRepository(TicketFilterSubscription::class);
                $forAgentIds      = $subscriptionRepo->getSubscribedActiveAgentIds();

                $changeSet   = $this->getContainer()->getTicketFilterChangeDetector()->getFilterChangeSet($ticket, $context, $forAgentIds);
                $listBuilder = new AgentNotifyListBuilder($ticket, $changeSet, $subscriptionRepo);
                $listBuilder->setLogger($context->getLogger());

                $notify = $listBuilder->genNotifyList();
                foreach ($notify as $n) {
                    $agents[] = $n['agent'];
                }
            } else {
                $agentData = $this->getContainer()->getAgentData();
                $agents    = array_merge($agents, $agentData->selectAgents($aid, $context->getPersonContext(), $ticket));
            }
        }

        $uniqueAgents = [];
        foreach ($agents as $agent) {
            $uniqueAgents[$agent->getId()] = $agent;
        }

        foreach ($agents as $agent) {
            if ($agent->PermissionsManager->TicketChecker->canView($ticket)) {
                $this->saveFlag(
                    $this->getContainer()->getDb(),
                    $ticket,
                    $agent,
                    $this->getActionOption('color')
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->saveFlag(
            $this->getContainer()->getDb(),
            $ticket,
            $person,
            $this->getActionOption('color')
        );
    }
}
