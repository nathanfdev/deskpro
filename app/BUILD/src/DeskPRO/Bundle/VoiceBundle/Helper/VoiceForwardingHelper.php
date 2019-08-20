<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class VoiceForwardingHelper.
 */
class VoiceForwardingHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;

    /**
     * @var VoiceProviderHelper
     */
    private $providerHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $onlineWorkers;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param StorageAdapterInterface $storageAdapter
     * @param VoiceProviderHelper     $providerHelper
     * @param LoggerInterface         $logger
     */
    public function __construct(
        EntityManager           $em,
        StorageAdapterInterface $storageAdapter,
        VoiceProviderHelper     $providerHelper,
        LoggerInterface         $logger
    ) {
        $this->em             = $em;
        $this->storageAdapter = $storageAdapter;
        $this->providerHelper = $providerHelper;
        $this->logger         = $logger;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param bool           $forceReloadOnlineWorkers
     */
    public function tryToMakeAForwardingCall(VoicePhoneCall $phoneCall, Person $agent, $forceReloadOnlineWorkers = false)
    {
        if (!$agent->canForwardCall()) {
            return;
        }

        if ($this->onlineWorkers === null || $forceReloadOnlineWorkers) {
            $this->onlineWorkers = $this->storageAdapter->getOnlineWorkersByType('agent');
        }

        $isAgentOnline = count(array_filter($this->onlineWorkers, function (Worker $onlineWorker) use ($agent) {
            return $onlineWorker->getTypeId() === $agent->getId();
        })) > 0;

        $this->logger->info(sprintf(
            '[VoiceForwardingHelper] Agent #%s is_online = %s',
            $agent->getId(), $isAgentOnline ? 'true' : 'false'
        ));

        if (($isAgentOnline && !$agent->getAgentData()->isForwardingLoggedOut()) || !$isAgentOnline) {
            // make an outbound call
            $this->logger->info(sprintf(
                '[VoiceForwardingHelper] Make a forwarding call to agent #%s',
                $agent->getId()
            ));

            $callUuid = $this->providerHelper->callForwardingNumber($phoneCall, $agent);
            if ($callUuid) {
                $phoneCall->addCallSid($agent->getId(), VoicePhoneCall::TYPE_FORWARDED, $callUuid);
                $this->em->flush();
            }
        }
    }
}
