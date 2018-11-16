<?php

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Chat\AgentChat;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Class SendAgentMention.
 */
class SendAgentMention extends AbstractContainerAwareAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $agentData         = $this->getContainer()->getAgentData();
        $notifyEmail       = [];
        $allNotifyAgentIds = [];

        foreach ($ticket->getStateChangeRecorder()->getNewAgentNotes() as $ticketMessage) {
            $originalMessage = $ticketMessage->getOriginalMessage();
            if (!preg_match_all('/<span[^>]+data-notify-agent-id="(\d+)"/i', $originalMessage, $matches, PREG_SET_ORDER)) {
                continue;
            }

            $notifyAgentIds = [];
            foreach ($matches as $match) {
                $notifyAgentIds[] = $match[1];
            }

            if ($notifyAgentIds) {
                $agentChat = new AgentChat($context->getPersonContext());
                $agentChat->disableOfflineEmailAlert(); // we'll handle offline notifs as part of normal notifications

                $notifyChat     = [];
                $notifyAgentIds = array_unique($notifyAgentIds);
                foreach ($notifyAgentIds as $agentId) {
                    if (!($agent = $agentData->get($agentId))) {
                        continue;
                    }
                    if (!$agent->PermissionsManager->TicketChecker->canView($ticket)) {
                        $context->getLogger()->debug(sprintf(
                            '[SendAgentMention] Agent #%d mentioned but has no permission to view ticket. Skip notifications for this agent.',
                            $agentId
                        ));
                        continue;
                    }

                    $notifyChat[$agent->getId()] = $agent;
                    $allNotifyAgentIds[]         = $agent->getId();

                    $pref = $agent->getPref('agent_notif.ticket_mention', 'always_send');
                    if ($pref == 'always_send' || ($pref == 'smart_send' && !$agentData->isAgentOnline($agent))) {
                        $notifyEmail[$agent->getId()] = $agent;
                    }
                }

                if ($notifyChat) {
                    $agentIds   = array_keys($notifyChat);
                    $notifyText = sprintf(
                        '%s alerted you in a note in {{t-%d}}: %s',
                        $ticketMessage->getPerson()->getDisplayName(),
                        $ticket->getId(),
                        $ticket->getSubject()
                    );
                    $agentChat->sendAgentMessage($notifyText, $agentIds);
                    if ($this->getContainer()->get('deskpro.feature_flags')->hasBeta('agent_chat')) {
                        $newIMText = sprintf(
                            '[{{t-%d}}] %s',
                            $ticket->getId(),
                            $ticketMessage->getMessageHtml()
                        );
                        $this->getContainer()->get('deskpro.notification.service')->sendNote(
                            $context->getPersonContext(),
                            $agentIds,
                            $newIMText
                        );
                    }
                }
            }
        }

        $context->getVars()->set('notified_agents', array_unique($allNotifyAgentIds));
        if ($notifyEmail) {
            $context->getVars()->set('mention_agents', $notifyEmail);
        }
    }
}
