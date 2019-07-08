<?php

namespace DpSys\LowScript;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait VoiceTaskRouterTrait.
 *
 * @method Connection getPdoRead()
 * @method Connection getVoicePdo()
 * @method ContainerInterface _getContainer()
 */
trait VoiceTaskRouterTrait
{
    protected function pingTaskRouterWorker($personId)
    {
        // todo support other storages
        $q = $this->getPdoRead()->prepare('
            SELECT a.available_status
            FROM agent_data a
            JOIN people p ON p.agent_data_id = a.id
            WHERE p.id = ?
        ');

        $q->execute([$personId]);

        $status = $q->fetchColumn();
        if ($status === AgentData::AVAILABLE_STATUS_IDLE) {
            $q = $this->getVoicePdo()->prepare('
            UPDATE voice_workers
            SET date_last_active = ?
            WHERE type = ? AND type_id = ?
        ');
            $q->execute([date('Y-m-d H:i:s', time()), 'agent', $personId]);
        }
    }

    /**
     * @return array
     */
    protected function getVoiceWorkersActivity()
    {
        $serializer     = $this->_getContainer()->get('serializer');
        $workerActivity = $this->_getContainer()->get('dp.voice.worker_activity');

        return $serializer->toArray($workerActivity->getActiveWorkers());
    }
}
