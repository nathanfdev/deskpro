<?php

namespace DpSys\LowScript;

/**
 * Class PingVoiceWorker.
 */
class PingVoiceWorker extends LowScriptAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        // todo support other storages
        $agentSession = $this->getAgentSession();

        $q = $this->getVoicePdo()->prepare('
            UPDATE voice_workers
            SET date_last_active = ?
            WHERE type = ? AND type_id = ?
        ');
        $q->execute([date('Y-m-d H:i:s', time()), 'agent', $agentSession['person_id']]);
    }
}
