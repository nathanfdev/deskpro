<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @var ContainerInterface
     */
    private $container;

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
     * @param ContainerInterface      $container
     * @param LoggerInterface         $logger
     */
    public function __construct(
        EntityManager           $em,
        StorageAdapterInterface $storageAdapter,
        VoiceTaskHelper         $taskHelper,
        ContainerInterface      $container,
        LoggerInterface         $logger
    ) {
        $this->em             = $em;
        $this->storageAdapter = $storageAdapter;
        $this->taskHelper     = $taskHelper;
        $this->container      = $container;
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
        $taskWorkers = $this->storageAdapter->getWorkers($task->getWorkerIds());

        foreach ($taskWorkers as $taskWorker) {
            /** @var Person $agent */
            $agent = $this->em->getRepository(Person::class)->find($taskWorker->getTypeId());
            if ($agent) {
                $this->container->get('dp.voice.forwarding_helper')->tryToMakeAForwardingCall($phoneCall, $agent);
            }
        }
    }
}
