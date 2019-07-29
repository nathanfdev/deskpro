<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LastPendingTaskListener.
 */
class LastPendingTaskListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::TIMEOUT        => 'updateLastPendingTaskTimeHandler',
            TaskRouterEvent::ERROR          => 'updateLastPendingTaskTimeHandler',
            TaskRouterEvent::TASK_COMPLETED => 'updateLastPendingTaskTimeHandler',
            TaskRouterEvent::TASK_CANCELED  => 'updateLastPendingTaskTimeHandler',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function updateLastPendingTaskTimeHandler(TaskRouterEvent $event)
    {
        $task = $event->getTask();

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);

        if ($task->getChannel() === VoiceWorkflow::getChannelName()) {
            $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_LAST_PENDING_VOICE_TASK_TIMESTAMP, time());
        } elseif ($task->getChannel() === ChatWorkflow::getChannelName()) {
            $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_LAST_PENDING_CHAT_TASK_TIMESTAMP, time());
        }
    }
}
