<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AgentNotifyListener.
 */
class VoiceAgentNotifyListener implements EventSubscriberInterface
{
    /**
     * @var
     */
    private $em;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param VoiceTaskHelper          $taskHelper
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     * @param Serializer               $serializer
     */
    public function __construct(
        EntityManager            $em,
        VoiceTaskHelper          $taskHelper,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher,
        Serializer               $serializer
    ) {
        $this->em         = $em;
        $this->taskHelper = $taskHelper;
        $this->storage    = $storage;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ASSIGNED => 'onAssigned',
            TaskRouterEvent::ACCEPTED => 'onAccepted',
            TaskRouterEvent::CANCELED => 'onCanceled',
            TaskRouterEvent::REJECTED => 'onCanceled',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onAssigned(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        // phone call
        $context = new SideloadSerializationContext();
        $context->setIncludes(['recording_enabled']);
        $context->setInlineSideloads(true);

        $serializedPhoneCall = $this->serializer->toArray(new ApiWrapper($phoneCall), $context)['data'];
        unset($serializedPhoneCall['ticket']);

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.incoming-call',
            [
                'account_id'         => $phoneCall->getNumber()->getAccount()->getId(),
                'number'             => $phoneCall->getExternalNumber(),
                'caller_person_id'   => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'            => $phoneCall->getId(),
                'task'               => $task->getId(),
                'queue_id'           => $task->getAttribute('queue'),
                'agent_id'           => $task->getAttribute('agent'),
                'related_people_ids' => $task->getAttribute('related_people'),
                'call_type'          => $task->getAttribute('transfer') ? 'transfer' : null,
                'invite_type'        => $task->getAttribute('invite_type') ?: null,
                'from_agent_id'      => $task->getAttribute('from_agent_id') ?: null,
                'phone_call'         => $serializedPhoneCall,
                'target'             => array_map(function (Worker $worker) {
                    return $worker->getTypeId();
                }, $this->storage->getWorkers($task->getWorkerIds())),
            ]
        ));
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onAccepted(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $acceptedWorker = $this->storage->getWorker($task->getAcceptedWorkerId());
        if (!$acceptedWorker) {
            return;
        }

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.incoming-call-answered',
            [
                'number'             => $phoneCall->getExternalNumber(),
                'caller_person_id'   => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'            => $phoneCall->getId(),
                'conference_sid'     => $phoneCall->getConferenceSid(),
                'task'               => $task->getId(),
                'related_people_ids' => $task->getAttribute('related_people'),
                'accepted_agent_id'  => $acceptedWorker->getTypeId(),
            ]
        ));
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onCanceled(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.incoming-call-rejected',
            [
                'number'             => $phoneCall->getExternalNumber(),
                'caller_person_id'   => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'            => $phoneCall->getId(),
                'conference_sid'     => $phoneCall->getConferenceSid(),
                'task'               => $task->getId(),
                'related_people_ids' => $task->getAttribute('related_people'),
            ]
        ));
    }
}
