<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use Doctrine\ORM\EntityManager;

/**
 * Class ChatTaskHelper.
 */
class ChatTaskHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ChatSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param ChatSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, ChatSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param Task $task
     *
     * @return ChatConversation|null
     */
    public function getChat(Task $task)
    {
        $chatId = $task->getAttribute('chat');
        if (!$chatId) {
            return;
        }

        return $this->em->getRepository(ChatConversation::class)->find($chatId);
    }

    /**
     * @param Task $task
     *
     * @return UserChatQueue|null
     */
    public function getChatQueue(Task $task)
    {
        $chat = $this->getChat($task);
        if (!$chat) {
            return;
        }

        $chatQueue = null;

        // get from chat department
        $department = $chat->getDepartment();
        if ($department) {
            $chatQueue = $department->getChatQueue();
        }

        // get default chat queue
        if (!$chatQueue) {
            $defaultQueueId = $this->settingsResolver->getDefaultQueue();
            if ($defaultQueueId) {
                $chatQueue = $this->em->getRepository(UserChatQueue::class)->find($defaultQueueId);
            }
        }

        // get any queue
        if (!$chatQueue) {
            $chatQueue = $this->em->getRepository(UserChatQueue::class)->findOneBy([]);
        }

        return $chatQueue;
    }
}
