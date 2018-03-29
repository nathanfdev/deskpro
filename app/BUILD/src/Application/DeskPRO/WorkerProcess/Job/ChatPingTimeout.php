<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Log\Logger;

/**
 * This cycles through chats and cleans up abandonded ones.
 */
class ChatPingTimeout extends AbstractJob
{
    const DEFAULT_INTERVAL = 30; // 30 secs (though cron prolly only possible to do 1 min)

    public function run()
    {
        /** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chat_manager = App::getSystemObject('user_chat_manager', ['session' => null]);

        //------------------------------
        // Agent timeouts
        //------------------------------

        $cutoff = date('Y-m-d H:i:s', time() - 360);

        // Agnets who we know are online
        $agent_ids = App::getDb()->fetchAllCol('
            SELECT sessions.person_id
            FROM sessions
            JOIN people ON people.id = sessions.person_id
            WHERE people.is_agent = 1 AND sessions.date_last > ?
        ', [$cutoff]);

        $agent_ids[] = 0;

        // Get all open chats belonging to agents who arent online/have timed out
        $timeouts = App::getDb()->fetchAllKeyValue("
            SELECT c.id, c.agent_id
            FROM chat_conversations c
            WHERE c.status = 'open' AND c.agent_id NOT IN (?)
        ", [$agent_ids], [Connection::PARAM_INT_ARRAY]);

        $count_agents = 0;
        foreach ($timeouts as $chat_id => $agent_id) {
            $chat  = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
            $agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);
            $chat_manager->agentTimeout($chat, $agent);

            ++$count_agents;
            $this->logger->log("Agent {$agent->id} {$agent->display_name} timed out in chat {$chat->id}", Logger::INFO);
        }

        \Application\DeskPRO\Chat\UserChat\AvailableTrigger::update();

        //------------------------------
        // User timeouts
        //------------------------------

        $cutoff = time() - 1200;

        $chat_ids = App::getDb()->fetchAllCol("
            SELECT DISTINCT c.id
            FROM chat_conversations c
            LEFT JOIN chat_conversation_pings AS p ON (p.chat_id = c.id AND p.ping_time > ?)
            WHERE c.status = 'open' AND c.is_agent = 0 AND p.id IS NULL
        ", [$cutoff]);

        $count_users = 0;
        while ($chat_id = array_pop($chat_ids)) {
            $chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
            $chat_manager->userTimeout($chat);

            ++$count_users;
            $this->logger->log("User timed out in chat {$chat->id}", Logger::INFO);
        }

        //------------------------------
        // Max waiting times
        //------------------------------

        $max_time   = App::getSetting('core_chat.max_wait_time');
        $count_wait = 0;

        if ($max_time) {
            $timesnip = date('Y-m-d H:i:s', time() - $max_time);
            $chat_ids = App::getDb()->fetchAllCol("
                SELECT id
                FROM chat_conversations c
                WHERE c.status = 'open' AND c.date_user_waiting < ?
            ", [$timesnip]);

            while ($chat_id = array_pop($chat_ids)) {
                $chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
                $chat_manager->waitTimeout($chat);

                ++$count_wait;
                $secs = time() - $chat->date_user_waiting->getTimestamp();
                $this->logger->log("Wait timed out chat {$chat->id} (waiting $secs seconds)", Logger::INFO);
            }
        }

        //------------------------------
        // Abandoned chats after user timeout
        //------------------------------

        $max_time        = App::getSetting('core_chat.abandoned_time');
        $count_abandoned = 0;

        if ($max_time) {
            $timesnip = date('Y-m-d H:i:s', time() - $max_time);
            $chat_ids = App::getDb()->fetchAllCol("
                SELECT id
                FROM chat_conversations c
                WHERE c.status = 'ended' AND c.ended_by = 'timeout' AND c.date_ended < ?
            ", [$timesnip]);

            while ($chat_id = array_pop($chat_ids)) {
                $chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
                $chat_manager->userAbandoned($chat);

                ++$count_abandoned;
                $secs = time() - $chat->date_ended->getTimestamp();
                $this->logger->log("Timed out user abandoned chat {$chat->id} (its been $secs seconds)", Logger::INFO);
            }
        }

        if ($count_agents || $count_users) {
            $this->logStatus("Chat timeouts: {$count_agents} agents, {$count_users} users, {$count_wait} wait", [
                'count_agents' => $count_agents,
                'count_users'  => $count_users,
                'count_wait'   => $count_wait,
            ]);
        }
    }
}
