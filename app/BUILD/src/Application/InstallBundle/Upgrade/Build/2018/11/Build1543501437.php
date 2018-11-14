<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;

class Build1543501437 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE user_chat_queues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, routing_model VARCHAR(255) NOT NULL, answer_timeout INT NOT NULL, is_all_agents TINYINT(1) NOT NULL, max_queue_size INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE user_chat_queue_targets (id INT AUTO_INCREMENT NOT NULL, queue_id INT NOT NULL, agent_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, sort INT NOT NULL, type VARCHAR(30) NOT NULL, INDEX IDX_7BC43B09477B5BAE (queue_id), INDEX IDX_7BC43B093414710B (agent_id), INDEX IDX_7BC43B09FB3FBA04 (agent_team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE user_chat_queue_targets ADD CONSTRAINT FK_7BC43B09477B5BAE FOREIGN KEY (queue_id) REFERENCES user_chat_queues (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE user_chat_queue_targets ADD CONSTRAINT FK_7BC43B093414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE user_chat_queue_targets ADD CONSTRAINT FK_7BC43B09FB3FBA04 FOREIGN KEY (agent_team_id) REFERENCES agent_teams (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE chat_conversations ADD task_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE departments ADD chat_queue_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE departments ADD CONSTRAINT FK_16AEB8D473598C9E FOREIGN KEY (chat_queue_id) REFERENCES user_chat_queues (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_16AEB8D473598C9E ON departments (chat_queue_id)');
        $this->execDbQuery('voice', 'ALTER TABLE voice_workers ADD pending_tasks LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD active_tasks LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->execDbQuery('voice', 'ALTER TABLE voice_tasks ADD date_expire_assigned DATETIME DEFAULT NULL');
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        // convert legacy Round Robin to the new Task Router queues
        $roundRobins = $db->fetchAll('SELECT * FROM chat_round_robin');
        foreach ($roundRobins as $roundRobin) {
            $routingModel = UserChatQueue::ROUTING_MODEL_ROUND_ROBIN;
            if ($roundRobin['routing_type'] === 1) {
                $routingModel = UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED;
            }

            $db->insert('user_chat_queues', [
                'name'          => $roundRobin['title'],
                'routing_model' => $routingModel,
            ]);

            $chatQueueId = $db->lastInsertId();

            $roundRobinAgents = $db->fetchAll('SELECT * FROM chat_round_robin_agents WHERE robin_id = ?', [$roundRobin['id']]);
            foreach ($roundRobinAgents as $roundRobinAgent) {
                $db->insert('user_chat_queue_targets', [
                    'queue_id' => $chatQueueId,
                    'agent_id' => $roundRobinAgent['agent_id'],
                    'sort'     => $roundRobinAgent['sort'],
                    'type'     => 'agent',
                ]);
            }

            $roundRobinDepartments = $db->fetchAll('SELECT * FROM chat_round_robin_to_department WHERE chat_round_robin_id = ?', [$roundRobin['id']]);
            foreach ($roundRobinDepartments as $roundRobinDepartment) {
                $db->update('departments', [
                    'chat_queue_id' => $chatQueueId,
                ], [
                    'id' => $roundRobinDepartment['department_id'],
                ]);
            }

            if ($roundRobin['apply_by_default']) {
                $db->replace('settings', [
                    'name'  => ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE,
                    'value' => $chatQueueId,
                ]);
            }
        }
    }
}
