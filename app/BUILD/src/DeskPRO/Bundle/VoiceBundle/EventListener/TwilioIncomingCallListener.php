<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TwilioIncomingCallListener.
 */
class TwilioIncomingCallListener implements EventSubscriberInterface
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
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param StorageAdapterInterface $storageAdapter
     * @param VoiceTaskHelper         $taskHelper
     * @param TwilioAdapter           $twilioAdapter
     * @param LoggerInterface         $logger
     */
    public function __construct(
        EntityManager           $em,
        StorageAdapterInterface $storageAdapter,
        VoiceTaskHelper         $taskHelper,
        TwilioAdapter           $twilioAdapter,
        LoggerInterface         $logger
    ) {
        $this->em             = $em;
        $this->storageAdapter = $storageAdapter;
        $this->taskHelper     = $taskHelper;
        $this->twilioAdapter  = $twilioAdapter;
        $this->logger         = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ASSIGNED => 'onAssigned',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     *
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAssigned(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if ($task->getChannel() !== VoiceWorkflow::getChannelName()) {
            return;
        }
        if (!$task->getWorkerIds()) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof TwilioVoiceAccount) {
            return;
        }

        $this->logger->info(sprintf(
            '[TwilioIncomingCallListener] Assigned to workers = %s',
            implode(', ', $task->getWorkerIds())
        ));

        // once workers are found
        // we can call them and enqueue the user
        $taskWorkers   = $this->storageAdapter->getWorkers($task->getWorkerIds());
        $onlineWorkers = $this->storageAdapter->getOnlineWorkersByType('agent');

        foreach ($taskWorkers as $taskWorker) {
            /** @var Person $agent */
            $agent = $this->em->getRepository(Person::class)->find($taskWorker->getTypeId());
            if ($agent) {
                if ($agent->canForwardCall()) {
                    $isAgentOnline = count(array_filter($onlineWorkers, function (Worker $onlineWorker) use ($taskWorker) {
                        return $onlineWorker->getTypeId() === $taskWorker->getTypeId();
                    })) > 0;

                    $this->logger->info(sprintf(
                        '[TwilioIncomingCallListener] Agent #%s is_online = %s',
                        $agent->getId(), $isAgentOnline ? 'true' : 'false'
                    ));

                    if (($isAgentOnline && !$agent->getAgentData()->isForwardingLoggedOut()) || !$isAgentOnline) {
                        // make an outbound call
                        $this->logger->info(sprintf(
                            '[TwilioIncomingCallListener] Make a forwarding call to agent #%s',
                            $agent->getId()
                        ));

                        $callUuid = $this->twilioAdapter->callForwardingNumber($phoneCall, $agent);
                        if ($callUuid) {
                            $phoneCall->addCallSid($agent->getId(), VoicePhoneCall::TYPE_FORWARDED, $callUuid);
                        }
                    }
                }
            }
        }

        $this->em->flush();
    }
}
