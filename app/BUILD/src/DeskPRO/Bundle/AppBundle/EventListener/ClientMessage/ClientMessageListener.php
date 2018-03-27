<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\ClientMessage;

use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ClientMessageListener.
 */
class ClientMessageListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param Serializer    $serializer
     */
    public function __construct(EntityManager $em, Serializer $serializer)
    {
        $this->em         = $em;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ClientMessageEvent::SEND => 'onSendMessage',
        ];
    }

    /**
     * @param ClientMessageEvent $event
     */
    public function onSendMessage(ClientMessageEvent $event)
    {
        $data = $event->getData();
        if (is_object($data)) {
            $data = $this->serializer->toArray($data, new SideloadSerializationContext());
        }

        $dispatcher = $event->getDispatcher();
        $dispatcher->dispatch(UserChatEvent::EVENT_NAME, new UserChatEvent($event->getChannel(), $data ?: []));
    }
}
