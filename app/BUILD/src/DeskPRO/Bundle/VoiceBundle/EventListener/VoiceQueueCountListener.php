<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
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
     * Constructor.
     *
     * @param VoiceTaskHelper         $taskHelper
     * @param StorageAdapterInterface $storage
     */
    public function __construct(VoiceTaskHelper $taskHelper, StorageAdapterInterface $storage)
    {
        $this->taskHelper = $taskHelper;
        $this->storage    = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ACCEPTED => 'onAccepted',
        ];
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

        $voiceQueue = $this->taskHelper->getVoiceQueue($task);
        if (!$voiceQueue) {
            return;
        }

        // if the call came from a voice queue
        // then update calls count stat to handle routing model strategies
        $taskQueue = $this->storage->getTaskQueue(VoiceWorkflow::getChannelName(), $voiceQueue->getId());
        if ($taskQueue) {
            $callsCount = $taskQueue->getAttribute('answered_calls_counts') ?: [];
            $worker     = $this->storage->getWorker($task->getAcceptedWorkerId());

            if (!isset($callsCount[$worker->getTypeId()])) {
                $callsCount[$worker->getTypeId()] = 0;
            }

            ++$callsCount[$worker->getTypeId()];
            $taskQueue->setAttribute('answered_calls_counts', $callsCount);

            $this->storage->saveTaskQueue($taskQueue);
        }
    }
}
