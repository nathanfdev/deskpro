<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceWorkerListener.
 */
class VoiceWorkerListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var StorageAdapterInterface
     */
    private $voiceStorage;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     * @param StorageAdapterInterface  $voiceStorage
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher, StorageAdapterInterface $voiceStorage)
    {
        $this->em           = $em;
        $this->dispatcher   = $dispatcher;
        $this->voiceStorage = $voiceStorage;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param AgentData $agentData
     */
    public function createWorker(AgentData $agentData)
    {
        // voice disabled
        if (!$agentData->isVoiceEnabled()) {
            return;
        }

        $agentData->setAvailableStatus(AgentData::AVAILABLE_STATUS_OFFLINE);

        $person = $agentData->getPerson();
        if ($person) {
            // create a voice worker for the agent
            if (!$this->voiceStorage->getWorkerByType('agent', $person->getId())) {
                $worker = new Worker();
                $worker->setType('agent');
                $worker->setTypeId($person->getId());
                $worker->setActivity(Worker::ACTIVITY_OFFLINE);

                $this->voiceStorage->saveWorker($worker);
            }

            // force reload agent's interface to show voice UI components with a spinner before real sync
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $person->getId(),
            ]));
        }
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param AgentData          $agentData
     * @param PreUpdateEventArgs $args
     */
    public function updateWorker(AgentData $agentData, PreUpdateEventArgs $args)
    {
        $accounts = $this->em->getRepository(AbstractVoiceAccount::class)->findAll();
        $person   = $agentData->getPerson();

        if (!count($accounts) || !$person) {
            return;
        }

        if ($args->hasChangedField('isVoiceEnabled')) {
            // create or delete worker
            if ($agentData->isVoiceEnabled()) {
                $this->createWorker($agentData);
            } else {
                $this->deleteWorker($agentData);
            }
        }
        if ($args->hasChangedField('outboundCallsEnabled') || $args->hasChangedField('canUseForwarding')) {
            // force reload agent's interface to show/hide outbound dialpad

            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $person->getId(),
            ]));
        }

        // enable or disable agent worker
        if ($args->hasChangedField('agentCallsEnabled')) {
            $worker = $this->voiceStorage->getWorkerByType('agent', $person->getId());
            if ($worker) {
                if ($agentData->isAgentCallsEnabled() && !$worker->isAvailable()) {
                    $worker->setActivity(Worker::ACTIVITY_IDLE);
                    $this->voiceStorage->saveWorker($worker);
                } elseif (!$agentData->isAgentCallsEnabled() && !$worker->isOffline()) {
                    $worker->setActivity(Worker::ACTIVITY_OFFLINE);
                    $this->voiceStorage->saveWorker($worker);
                }
            }
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param AgentData $agentData
     */
    public function deleteWorker(AgentData $agentData)
    {
        $accounts = $this->em->getRepository(AbstractVoiceAccount::class)->findAll();
        $person   = $agentData->getPerson();

        if (!count($accounts) || !$person) {
            return;
        }

        // unset voice data
        $agentData->setExtensionNumber(null);

        // remove agent from queues
        $this->em->getConnection()->executeQuery('DELETE FROM voice_queue_agents WHERE agent_id = :person_id', [
            'person_id' => $person->getId(),
        ]);

        // remove specific agent targets
        $this->em->getConnection()->executeQuery('DELETE FROM voice_targets WHERE agent_id = :person_id', [
            'person_id' => $person->getId(),
        ]);

        // remove agent's voice worker
        $this->voiceStorage->removeWorker('agent', $person->getId());

        // force reload agent's interface to hide voice UI components before real sync
        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
            'type'        => 'admin',
            'person_id'   => 0,
            'person_name' => 'System',
            'target'      => $person->getId(),
        ]));
    }
}
