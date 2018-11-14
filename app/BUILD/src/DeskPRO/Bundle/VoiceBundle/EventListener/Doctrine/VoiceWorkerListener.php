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
     * @ORM\PreUpdate()
     *
     * @param AgentData          $agentData
     * @param PreUpdateEventArgs $args
     */
    public function updateWorker(AgentData $agentData, PreUpdateEventArgs $args)
    {
        $person = $agentData->getPerson();
        if (!$person) {
            return;
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
        if ($args->hasChangedField('availableStatus')) {
            $worker = $this->voiceStorage->getWorkerByType('agent', $person->getId());
            if ($worker) {
                if ($agentData->getAvailableStatus() === AgentData::AVAILABLE_STATUS_IDLE) {
                    $worker->setActivity(Worker::ACTIVITY_IDLE);
                } else {
                    $worker->setActivity(Worker::ACTIVITY_OFFLINE);
                }

                $this->voiceStorage->saveWorker($worker);
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

        // force reload agent's interface to hide voice UI components before real sync
        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
            'type'        => 'admin',
            'person_id'   => 0,
            'person_name' => 'System',
            'target'      => $person->getId(),
        ]));
    }
}
