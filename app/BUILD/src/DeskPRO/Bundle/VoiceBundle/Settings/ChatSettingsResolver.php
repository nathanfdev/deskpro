<?php

namespace DeskPRO\Bundle\VoiceBundle\Settings;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Settings\Model\ChatQueueSettings;

/**
 * Class ChatSettingsResolver.
 */
class ChatSettingsResolver
{
    const USER_CHAT_AGENT_TIMEOUT   = 'chat.agent_timeout';
    const USER_CHAT_MAX_CHATS_COUNT = 'chat.max_chats_count';
    const USER_CHAT_DEFAULT_QUEUE   = 'chat.default_queue';

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @return ChatQueueSettings
     */
    public function getChatQueueSettings()
    {
        $model = new ChatQueueSettings();
        $model
            ->setAgentTimeout($this->getAgentChatTimeout())
            ->setMaxChatsCount($this->getMaxChatsCount())
            ->setDefaultQueue($this->getDefaultQueue())
        ;

        return $model;
    }

    /**
     * @return int
     */
    public function getAgentChatTimeout()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::USER_CHAT_AGENT_TIMEOUT, 300);
    }

    /**
     * @return int
     */
    public function getMaxChatsCount()
    {
        return $this->settingsResolver->getGlobalSettings()->get(self::USER_CHAT_MAX_CHATS_COUNT, 1);
    }

    /**
     * @return int|null
     */
    public function getDefaultQueue()
    {
        $defaultQueue = $this->settingsResolver->getGlobalSettings()->get(self::USER_CHAT_DEFAULT_QUEUE);

        return $defaultQueue ? (int) $defaultQueue : null;
    }
}
