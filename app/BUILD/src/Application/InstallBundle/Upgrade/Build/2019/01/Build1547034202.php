<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;

class Build1547034202 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        // no chat queue exists
        // create a new default one
        $chatQueues = $db->fetchAll('SELECT * FROM user_chat_queues');
        if (!count($chatQueues)) {
            $db->insert('user_chat_queues', [
                'name'           => 'Default',
                'routing_model'  => UserChatQueue::ROUTING_MODEL_SIMULRING,
                'answer_timeout' => 60,
                'is_all_agents'  => 1,
                'max_queue_size' => 0,
            ]);

            $chatQueueId = $db->lastInsertId();
            $db->replace('settings', [
                'name'  => ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE,
                'value' => $chatQueueId,
            ]);
        }
    }
}
