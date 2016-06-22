<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\UserChat;

use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserChatSystemListener.
 */
class UserChatSystemListener implements EventSubscriberInterface
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
            UserChatEvent::STARTED        => 'onChatEvent',
            UserChatEvent::USER_RETURNED  => 'onChatEvent',
            UserChatEvent::ENDED          => 'onChatEvent',
            UserChatEvent::END_BY         => 'onChatEvent',
            UserChatEvent::END_BY_USER    => 'onChatEvent',
            UserChatEvent::SET_DEPARTMENT => 'onChatEvent',
            UserChatEvent::ASSIGNED       => 'onChatEvent',
            UserChatEvent::UNASSIGNED     => 'onChatEvent',
        ];
    }

    /**
     * Sends chat system message to a conversation.
     *
     * @param UserChatEvent $event
     */
    public function onChatEvent(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $chatMessage  = UserChatMessages::createSysMessage($event->getName(), $event);
        $conversation->addMessage($chatMessage);

        $this->em->persist($conversation);
        $this->em->flush();

        $channelType = $chatMessage->getIsUserHidden() ? 'newmessage_hidden' : 'newmessage';
        $channel     = $conversation->getChannelId($channelType);

        $event->getDispatcher()->dispatch(ClientMessageEvent::SEND, new ClientMessageEvent($channel, $chatMessage));
    }
}
