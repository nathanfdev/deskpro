<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Model\AverageWaitingTime;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VoiceQueueCountListener.
 */
class VoiceQueueCountListener implements EventSubscriberInterface
{
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
     * @param VoiceTaskHelper          $taskHelper
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     * @param Serializer               $serializer
     */
    public function __construct(
        VoiceTaskHelper          $taskHelper,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher,
        Serializer               $serializer
    ) {
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
            TaskRouterEvent::TASK_CREATED   => 'addWaitingUser',
            TaskRouterEvent::ACCEPTED       => 'removeWaitingUser',
            TaskRouterEvent::ERROR          => 'removeWaitingUser',
            TaskRouterEvent::TIMEOUT        => 'removeWaitingUser',
            TaskRouterEvent::TASK_COMPLETED => 'removeWaitingUser',
            TaskRouterEvent::TASK_CANCELED  => 'removeWaitingUser',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function addWaitingUser(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $voiceQueue = $this->taskHelper->getVoiceQueue($task);
        if (!$voiceQueue) {
            return;
        }

        $taskQueue = $this->storage->getTaskQueue(VoiceWorkflow::getChannelName(), $voiceQueue->getId());
        if (!$taskQueue) {
            return;
        }

        $waitingUser = [
            'task_id'      => $task->getId(),
            'date_created' => date('c'),
        ];

        if ($task->getAttribute('person')) {
            $waitingUser['person_id'] = $task->getAttribute('person');
        }

        $waitingUsers   = $taskQueue->getAttribute('waiting_users') ?: [];
        $waitingUsers[] = $waitingUser;

        $taskQueue->setAttribute('waiting_users', $waitingUsers);

        $this->storage->saveTaskQueue($taskQueue);
        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.queue.average-waiting-time',
            $this->serializer->toArray(new AverageWaitingTime($voiceQueue, $waitingUsers), new SideloadSerializationContext())
        ));
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function removeWaitingUser(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $voiceQueue = $this->taskHelper->getVoiceQueue($task);
        if (!$voiceQueue) {
            return;
        }

        $taskQueue = $this->storage->getTaskQueue(VoiceWorkflow::getChannelName(), $voiceQueue->getId());
        if (!$taskQueue) {
            return;
        }

        $waitingUsers = $taskQueue->getAttribute('waiting_users') ?: [];
        foreach ($waitingUsers as $index => $waitingUser) {
            if ($waitingUser['task_id'] === $task->getId()) {
                unset($waitingUsers[$index]);
            }
        }

        $taskQueue->setAttribute('waiting_users', $waitingUsers);

        $this->storage->saveTaskQueue($taskQueue);
        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.queue.average-waiting-time',
            $this->serializer->toArray(new AverageWaitingTime($voiceQueue, $waitingUsers), new SideloadSerializationContext())
        ));
    }
}
