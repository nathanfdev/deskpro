<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Event;

use Application\DeskPRO\Entity\Brand;
use Symfony\Component\EventDispatcher\Event;

class ChatSettingsUpdatedEvent extends Event
{
    public const CHAT_SETTINGS_UPDATED = 'messenger.chat.settings.updated';

    /**
     * @var Brand
     */
    protected $brand;


    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }


    public function getBrand(): Brand
    {
        return $this->brand;
    }
}
