<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class WorkerHelper.
 */
class WorkerHelper
{
    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param StorageAdapterInterface $storage
     * @param EntityManager           $em
     */
    public function __construct(StorageAdapterInterface $storage, EntityManager $em)
    {
        $this->storage = $storage;
        $this->em      = $em;
    }

    /**
     * @param string $workerType
     * @param int    $workerId
     */
    public function setIdle($workerType, $workerId)
    {
        $this->changeActivity($workerType, $workerId, Worker::ACTIVITY_IDLE);
    }

    /**
     * @param string $workerType
     * @param int    $workerId
     * @param string $activity
     */
    public function changeActivity($workerType, $workerId, $activity)
    {
        $worker = $this->storage->getWorkerByType($workerType, $workerId);
        if (!$worker) {
            return;
        }

        $worker->setActivity($activity);
        $this->storage->saveWorker($worker);
    }

    /**
     * @param Person $person
     */
    public function updateWorkerActivityOnBootstrap(Person $person)
    {
        $agentData = $person->getAgentData();
        if (!$agentData) {
            return;
        }

        $worker = $this->storage->getWorkerByType('agent', $person->getId());
        if (!$worker) {
            return;
        }

        $isVoiceEnabled = $agentData->isVoiceEnabled() && $agentData->isAgentCallsEnabled();
        if ($worker->isOffline() && $isVoiceEnabled) {
            $this->setIdle('agent', $person->getId());
        }
    }

    /**
     * @return Worker[]
     */
    public function getForwardingCallWorkers()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('a')
            ->from(AgentData::class, 'a')
            ->where(
                'a.isVoiceEnabled = 1',
                'a.agentCallsEnabled = 1',
                'a.agentCanUseForwarding = 1',
                "a.forwardingNumber IS NOT NULL AND a.forwardingNumber != ''"
            )
        ;

        $agentData = $qb->getQuery()->getResult();
        $agentIds  = array_map(function (AgentData $agentData) {
            return $agentData->getPerson()->getId();
        }, $agentData);

        return $this->storage->getWorkersByType('agent', $agentIds);
    }
}
