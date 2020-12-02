<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\EventListener;

use Application\DeskPRO\Entity\BrandSetting;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatSettingsUpdatedEvent;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class ChatSettingsSubscriber implements EventSubscriberInterface
{
    const CORE_APPS_CHAT = 'core.apps_chat';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MessengerSettingsResolver
     */
    private MessengerSettingsResolver $messengerSettingsResolver;

    /**
     * ChatSettingsSubscriber constructor.
     * @param EntityManagerInterface $em
     * @param MessengerSettingsResolver $messengerSettingsResolver
     */
    public function __construct(
        EntityManagerInterface $em,
        MessengerSettingsResolver $messengerSettingsResolver
    ) {
        $this->em = $em;
        $this->messengerSettingsResolver = $messengerSettingsResolver;
    }

    /**
     * @return array|array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ChatSettingsUpdatedEvent::CHAT_SETTINGS_UPDATED => ['onChatSettingsUpdate', 0],
        ];
    }

    /**
     * @param ChatSettingsUpdatedEvent $chatSettingsUpdatedEvent
     */
    public function onChatSettingsUpdate(ChatSettingsUpdatedEvent $chatSettingsUpdatedEvent): void
    {
        $brand        = $chatSettingsUpdatedEvent->getBrand();
        $brandModel   = $this->messengerSettingsResolver->getMessengerSettings($brand);
        $brandsetting = $this->em->getRepository(BrandSetting::class)->findOneBy(['brand' => $brand, 'name' => self::CORE_APPS_CHAT]);

        if (null === $brandsetting) {
            $brandsetting   = new BrandSetting();
            $brandsetting->name  = self::CORE_APPS_CHAT;
            $brandsetting->brand = $brand;
        }

        $brandsetting->value = $brandModel->getChat()->isEnabled();
        $this->em->persist($brandsetting);

        $this->em->flush();
    }

}
