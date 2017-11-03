<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
