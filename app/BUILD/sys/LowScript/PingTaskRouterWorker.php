<?php

namespace DpSys\LowScript;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;

/**
 * Class PingTaskRouterWorker.
 */
class PingTaskRouterWorker extends LowScriptAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        // todo support other storages
        $agentSession = $this->getAgentSession();
        $agentId      = $agentSession['person_id'];

        $q = $this->getPdoRead()->prepare('
            SELECT a.available_status
            FROM agent_data a
            JOIN people p ON p.agent_data_id = a.id
            WHERE p.id = ?
        ');

        $q->execute([$agentId]);

        $status = $q->fetchColumn();
        if ($status === AgentData::AVAILABLE_STATUS_IDLE) {
            $q = $this->getVoicePdo()->prepare('
            UPDATE voice_workers
            SET date_last_active = ?
            WHERE type = ? AND type_id = ?
        ');
            $q->execute([date('Y-m-d H:i:s', time()), 'agent', $agentId]);
        }
    }
}
