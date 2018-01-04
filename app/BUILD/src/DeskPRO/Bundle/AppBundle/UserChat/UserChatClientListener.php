<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserChatClientListener.
 */
class UserChatClientListener implements EventSubscriberInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

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
            UserChatEvent::SEND_MESSAGE   => 'onSendMessage',
            UserChatEvent::USER_TRACK     => 'onUserTrack',
            UserChatEvent::ACK_MESSAGES   => 'onAckMessages',
            UserChatEvent::USER_TYPING    => 'onUserTyping',
        ];
    }

    /**
     * @param UserChatEvent $event
     */
    public function onStarted(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_NEW, $event->getChat());
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUserReturned(UserChatEvent $event)
    {
        $data = array_merge($this->getInfo($event), [
            'restarted' => true,
        ]);

        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_NEW, $data);
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEnded(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEndedBy(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onEndedByUser(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_ENDED, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onSetDepartment(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_DEPARTMENT_CHANGE, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onAssigned(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_REASSIGNED, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUnassigned(UserChatEvent $event)
    {
        $this->send($event, ClientMessageEvent::CHANNEL_CHAT_UNASSIGNED, $this->getInfo($event));
    }

    /**
     * @param UserChatEvent $event
     */
    public function onSendMessage(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $message      = $event->getData();
        $channel      = $conversation->getChannelId('newmessage');

        $this->send($event, $channel, $message);
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUserTrack(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $message      = $event->getData();
        $channel      = $conversation->getChannelId('hidden_newmessage');

        $this->send($event, $channel, $message);
    }

    /**
     * @param UserChatEvent $event
     */
    public function onAckMessages(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $message_ids  = $event->getData();
        $channel      = $conversation->getChannelId('ack_messages');

        $this->send($event, $channel, ['message_ids' => $message_ids]);
    }

    /**
     * @param UserChatEvent $event
     */
    public function onUserTyping(UserChatEvent $event)
    {
        $conversation   = $event->getChat();
        $preview_string = $event->getData();
        $channel        = $conversation->getChannelId('usertyping');

        $this->send($event, $channel, ['preview' => $preview_string]);
    }

    /**
     * @param UserChatEvent $event
     *
     * @return array
     */
    protected function getInfo(UserChatEvent $event)
    {
        return $this->serializer->toArray($event->getChat(), new SideloadSerializationContext());
    }

    /**
     * @param UserChatEvent $event
     * @param string        $channel
     * @param mixed         $data
     */
    protected function send(UserChatEvent $event, $channel, $data)
    {
        $data['conversation_id'] = $event->getChat()->getId();
        $event->getDispatcher()->dispatch(ClientMessageEvent::SEND, new ClientMessageEvent($channel, $data));
    }
}
