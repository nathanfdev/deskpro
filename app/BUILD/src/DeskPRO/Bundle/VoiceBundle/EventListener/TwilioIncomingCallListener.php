<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
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
     * @var AgentDataService
     */
    private $agentDataService;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param StorageAdapterInterface $storageAdapter
     * @param VoiceTaskHelper         $taskHelper
     * @param TwilioAdapter           $twilioAdapter
     * @param AgentDataService        $agentDataService
     */
    public function __construct(
        EntityManager           $em,
        StorageAdapterInterface $storageAdapter,
        VoiceTaskHelper         $taskHelper,
        TwilioAdapter           $twilioAdapter,
        AgentDataService        $agentDataService
    ) {
        $this->em               = $em;
        $this->storageAdapter   = $storageAdapter;
        $this->taskHelper       = $taskHelper;
        $this->twilioAdapter    = $twilioAdapter;
        $this->agentDataService = $agentDataService;
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

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $account = $phoneCall->getNumber()->getAccount();

        if ($account instanceof TwilioVoiceAccount) {
            if ($task->getWorkerIds()) {
                // once workers are found
                // we can call them and enqueue the user
                $workers = $this->storageAdapter->getWorkers($task->getWorkerIds());
                foreach ($workers as $worker) {
                    /** @var Person $agent */
                    $agent = $this->em->getRepository(Person::class)->find($worker->getTypeId());
                    if ($agent) {
                        if ($agent->canForwardCall()) {
                            $isAgentOnline = $this->agentDataService->isAgentOnline($agent);
                            if (($isAgentOnline && !$agent->getAgentData()->isForwardingLoggedOut()) || !$isAgentOnline) {
                                // make an outbound call
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
    }
}
