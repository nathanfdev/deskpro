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
    private $storageAdapter;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param VoiceTaskHelper         $taskHelper
     * @param StorageAdapterInterface $storageAdapter
     * @param ContainerInterface      $container
     */
    public function __construct(
        EntityManager           $em,
        VoiceTaskHelper         $taskHelper,
        StorageAdapterInterface $storageAdapter,
        ContainerInterface      $container
    ) {
        $this->em             = $em;
        $this->taskHelper     = $taskHelper;
        $this->storageAdapter = $storageAdapter;
        $this->container      = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ASSIGN_TIMEOUT => 'onAssignTimeout',
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

        $taskWorkers = $this->storageAdapter->getWorkers($task->getWorkerIds());
        foreach ($taskWorkers as $taskWorker) {
            /** @var Person $agent */
            $agent = $this->em->getRepository(Person::class)->find($taskWorker->getTypeId());
            if ($agent) {
                $this->container->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $agent);
            }
        }
    }
}
