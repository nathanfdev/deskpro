<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 *
 * @category ClientMessage
 */

namespace Application\DeskPRO\ClientMessage\Generator;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Chat
{
    public static function createNewAddedPartMessage(
        ChatConversation $conversation,
        Person $agent,
        EventDispatcherInterface $eventDispatcher
    ) {
        /** @var ChatMessage $chatMessage */
        $chatMessage = $conversation->getMessages()->get(0);

        $eventDispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent(
                'chat_user_agent.added-as-part',
                [
                    'conversation_id' => $conversation->getId(),
                    'message_id'      => $chatMessage->getId(),
                    'author_id'       => $chatMessage->getAuthorId(),
                    'author_name'     => $chatMessage->getAuthorName(),
                    'message'         => $chatMessage->getContent(),
                    'date_created'    => $chatMessage['date_created']->getTimestamp(),
                    'target'          => $agent->getId(),
                ]
        ));
    }

    public static function createPartisipatedUpdatedMessages(
        ChatConversation $conversation,
        EventDispatcherInterface $eventDispatcher
    ) {
        $cmData = [
            'conversation_id' => $conversation->getId(),
            'agent_id'        => $conversation['agent'] ? $conversation['agent']['id'] : 0,
            'participant_ids' => [],
        ];

        foreach ($conversation->getParticipants() as $participant) {
            $cmData['participant_ids'][] = $participant->getId();
        }

        if ($conversation->getAgent()) {
            $eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                'chat_user_agent.chat-parts-updated',
                array_merge($cmData, ['target' => $conversation->getAgent()->getId()])
            ));
        }

        // Participants first
        foreach ($conversation->getParticipants() as $participant) {
            $eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                'chat_user_agent.chat-parts-updated',
                array_merge($cmData, ['target' => $participant->getId()])
            ));
        }
    }

    public static function createNewMessageMessages(ChatMessage $chatMessage, EventDispatcherInterface $eventDispatcher)
    {
        $conversation = $chatMessage->getConversation();

        if ($conversation['is_agent']) {
            $channel = 'agent_chat.message';
        } else {
            $channel = 'chat.message';
        }

        $authorType = 'user';
        if ($chatMessage['is_sys']) {
            $authorType = 'sys';
        } elseif ($chatMessage['author'] and $chatMessage['author']['is_agent']) {
            $authorType = 'agent';
        }

        $cmData = [
            'conversation_id' => $conversation['id'],
            'message_id'      => $chatMessage['id'],
            'author_id'       => $chatMessage['author_id'],
            'author_name'     => $chatMessage['author_name'],
            'author_type'     => $authorType,
            'message'         => $chatMessage['content'],
            'date_created'    => $chatMessage['date_created']->getTimestamp(),
        ];
        if ($chatMessage['is_html']) {
            $cmData['message_html'] = $chatMessage['content'];
            unset($cmData['message']);
        }

        // Assigned agent
        if ($conversation->getAgent()) {
            $eventDispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent($channel, array_merge($cmData, ['target' => $conversation->getAgent()->getId()])
            ));
        }

        // Participants first
        foreach ($conversation->getParticipants() as $participant) {
            $eventDispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent($channel, array_merge($cmData, ['target' => $participant->getId()])
            ));
        }
    }
}
