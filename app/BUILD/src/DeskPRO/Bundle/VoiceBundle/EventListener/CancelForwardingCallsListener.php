<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CancelForwardingCallsListener.
 */
class CancelForwardingCallsListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param VoiceTaskHelper         $taskHelper
     * @param StorageAdapterInterface $storage
     * @param ContainerInterface      $container
     */
    public function __construct(
        EntityManager $em,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage,
        ContainerInterface $container
    ) {
        $this->em         = $em;
        $this->taskHelper = $taskHelper;
        $this->storage    = $storage;
        $this->container  = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ASSIGN_TIMEOUT => 'onAssignTimeout',
            TaskRouterEvent::ACCEPTED       => 'onAccepted',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onAssignTimeout(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        $taskWorkers = $this->storage->getWorkers($task->getWorkerIds());
        foreach ($taskWorkers as $taskWorker) {
            /** @var Person $agent */
            $agent = $this->em->getRepository(Person::class)->find($taskWorker->getTypeId());
            if ($agent) {
                $this->container->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $agent);
            }
        }
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

        $taskWorkers = $this->storage->getWorkers($task->getWorkerIds());
        foreach ($taskWorkers as $taskWorker) {
            if ($taskWorker->getId() === $task->getAcceptedWorkerId()) {
                continue;
            }

            /** @var Person $agent */
            $agent = $this->em->getRepository(Person::class)->find($taskWorker->getTypeId());
            if ($agent) {
                $this->container->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $agent);
            }
        }
    }
}
