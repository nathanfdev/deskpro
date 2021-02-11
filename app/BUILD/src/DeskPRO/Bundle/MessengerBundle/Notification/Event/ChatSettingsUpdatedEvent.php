<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Event;

use Application\DeskPRO\Entity\Brand;
use Symfony\Component\EventDispatcher\Event;

class ChatSettingsUpdatedEvent extends Event
{
    const CHAT_SETTINGS_UPDATED = 'messenger.chat.settings.updated';

    /**
     * @var Brand
     */
    protected $brand;

    /**
     * ChatSettingsUpdatedEvent constructor.
     *
     * @param Brand $brand
     */
    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
