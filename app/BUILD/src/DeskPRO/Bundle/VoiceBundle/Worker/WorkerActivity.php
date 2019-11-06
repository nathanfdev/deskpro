<?php

namespace DeskPRO\Bundle\VoiceBundle\Worker;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\VoiceBundle\Helper\WorkerHelper;
use DeskPRO\Bundle\VoiceBundle\Model\WorkerActivityStatus;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use Doctrine\ORM\EntityManager;

/**
 * Class WorkerActivity.
 */
class WorkerActivity
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var StorageAdapterInterface
     */
    private $voiceStorage;

    /**
     * @var WorkerHelper
     */
    private $workerHelper;

    /**
     * @var VoiceWorkflow
     */
    private $voiceWorkflow;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param StorageAdapterInterface $voiceStorage
     * @param WorkerHelper            $workerHelper
     * @param VoiceWorkflow           $voiceWorkflow
     */
    public function __construct(
        EntityManager           $em,
        StorageAdapterInterface $voiceStorage,
        WorkerHelper            $workerHelper,
        VoiceWorkflow           $voiceWorkflow
    ) {
        $this->em            = $em;
        $this->voiceStorage  = $voiceStorage;
        $this->workerHelper  = $workerHelper;
        $this->voiceWorkflow = $voiceWorkflow;
    }

    /**
     * @return WorkerActivityStatus[]
     */
    public function getActiveWorkers()
    {
        $workersActivity = [];

        /** @var Worker[] $workers */
        $workers       = [];
        $onlineWorkers = [];
        foreach ($this->voiceStorage->getOnlineWorkersByType('agent') as $worker) {
            $workers[$worker->getTypeId()]       = $worker;
            $onlineWorkers[$worker->getTypeId()] = true;
        }
        foreach ($this->workerHelper->getForwardingCallWorkers() as $worker) {
            $workers[$worker->getTypeId()] = $worker;
        }

        if ($workers) {
            $this->em->clear(AgentData::class);
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('a')
                ->from(AgentData::class, 'a')
                ->join('a.person', 'p')
                ->where('p.id IN (:ids)')
                ->setParameter('ids', array_keys($workers))
            ;

            /** @var AgentData[] $agentData */
            $agentData = $qb->getQuery()->getResult();

            $agentDataMap = [];
            foreach ($agentData as $agent) {
                $agentDataMap[$agent->getPerson()->getId()] = $agent;
            }

            foreach ($workers as $worker) {
                $agentId  = $worker->getTypeId();
                $isOnline = isset($onlineWorkers[$agentId]);

                if (!isset($agentDataMap[$agentId])) {
                    continue;
                }

                /** @var AgentData $agentData */
                $agentData = $agentDataMap[$agentId];

                $activityStatus = new WorkerActivityStatus();
                $activityStatus
                    ->setAgentId($worker->getTypeId())
                    ->setOnline($isOnline)
                    ->setVoiceEnabled($agentData->isVoiceEnabled() && $agentData->isAgentCallsEnabled())
                    ->setBusyForVoice($this->voiceWorkflow->workerIsBusy($worker))
                    ->setForwardingEnabled($agentData->canUseForwarding()
                        && $agentData->agentCanUseForwarding()
                        && ($isOnline && !$agentData->isForwardingLoggedOut()) || !$isOnline
                    )
                ;

                $workersActivity[] = $activityStatus;
            }
        }

        return $workersActivity;
    }
}
