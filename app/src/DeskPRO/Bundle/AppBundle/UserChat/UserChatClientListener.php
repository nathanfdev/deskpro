<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\UserChat;

use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserChatClientListener.
 */
class UserChatClientListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserChatEvent::STARTED        => 'onStarted',
            UserChatEvent::USER_RETURNED  => 'onUserReturned',
            UserChatEvent::ENDED          => 'onEnded',
            UserChatEvent::END_BY         => 'onEndedBy',
            UserChatEvent::END_BY_USER    => 'onEndedByUser',
            UserChatEvent::SET_DEPARTMENT => 'onSetDepartment',
            UserChatEvent::ASSIGNED       => 'onAssigned',
            UserChatEvent::UNASSIGNED     => 'onUnassigned',
        ];
    }

    /**
     * @param UserChatEvent $event
     */
    public function onStarted(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_NEW, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUserReturned(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_NEW, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEnded(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEndedBy(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEndedByUser(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onSetDepartment(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_DEPARTMENT_CHANGE, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onAssigned(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_REASSIGNED, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUnassigned(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_UNASSIGNED, $event->getConversation());
    }

    /**
     * @param UserChatEvent $event
     * @param string        $channel
     * @param mixed         $data
     */
    protected function send(UserChatEvent $event, $channel, $data)
    {
        $event->getDispatcher()->dispatch(ClientMessageEvent::SEND, new ClientMessageEvent($channel, $data));
    }
}
