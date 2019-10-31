<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VoicemailListener.
 */
class VoicemailListener implements EventSubscriberInterface
{
    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var StorageAdapterInterface
     */
    private $voiceStorage;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param VoiceTaskHelper         $taskHelper
     * @param StorageAdapterInterface $voiceStorage
     * @param ContainerInterface      $container
     */
    public function __construct(
        VoiceTaskHelper         $taskHelper,
        StorageAdapterInterface $voiceStorage,
        ContainerInterface      $container
    ) {
        $this->taskHelper   = $taskHelper;
        $this->voiceStorage = $voiceStorage;
        $this->container    = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::REJECTED => 'onRejected',
            TaskRouterEvent::TIMEOUT  => 'onTimeout',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onRejected(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if ($task->getChannel() !== VoiceWorkflow::getChannelName()) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        // if call target is an agent, redirect to voicemail immediately
        if ($taskAgent = $this->taskHelper->getWorkerAgent($task)) {
            // transfer call to voicemail
            $this->container->get('dp.voice.transfer_helper')->transferToVoicemail($phoneCall);

            // the task was redirected to voicemail
            // mark as canceled
            $task->setStatus(Task::STATUS_CANCELED);
        }
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onTimeout(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if ($task->getChannel() !== VoiceWorkflow::getChannelName()) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $userParticipant = $phoneCall->getUserParticipants()->first();
        if ($userParticipant) {
            $this->container->get('dp.voice.transfer_helper')->transferToVoicemail($phoneCall);
        }
    }
}
