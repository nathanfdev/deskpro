<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WorkerLastCallAtListener.
 */
class WorkerLastCallAtListener implements EventSubscriberInterface
{
    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * Constructor.
     *
     * @param StorageAdapterInterface $storage
     */
    public function __construct(StorageAdapterInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::COMPLETE_WORKER => 'updateLastCallAt',
        ];
    }

    /**
     * If the call came from a voice queue then update 'lastCallAt' property to handle routing model strategies.
     *
     * @internal
     *
     * @param TaskRouterEvent $event
     *
     * @throws \Exception
     */
    public function updateLastCallAt(TaskRouterEvent $event)
    {
        $worker = $event->getWorker();
        if (!$worker) {
            return;
        }

        $worker->setLastCallAt(new \DateTime());

        $this->storage->saveWorker($worker);
    }
}
