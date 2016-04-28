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

/**
 * DeskPRO.
 *
 * @category ClientMessage
 */

namespace Application\DeskPRO\ClientMessage\Generator;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;

class Chat
{
    public static function createNewChatMessages($byClientId, ChatConversation $conversation, ChatMessage $chatMessage)
    {
        if ($conversation['is_agent']) {
            $channel = 'agent_chat.new-chat';
        } else {
            $channel = 'chat.new-chat';
        }

        $newChatCm = new ClientMessage();
        $newChatCm->fromArray([
            'channel' => $channel,
            'data'    => [
                'conversation_id' => $conversation['id'],
                'message_id'      => $chatMessage['id'],
                'author_id'       => $chatMessage['author_id'],
                'author_name'     => $chatMessage['author_name'],
                'message'         => $chatMessage['content'],
                'date_created'    => $chatMessage['date_created']->getTimestamp(),
            ],
            'created_by_client' => $byClientId,
        ]);

        return [$newChatCm];
    }

    public static function createNewAddedPartMessage($byClientId, ChatConversation $conversation, $agent)
    {
        $channel = 'chat_user_agent.added-as-part';

        $chatMessage = $conversation->messages->get(0);

        $newChatCm = new ClientMessage();
        $newChatCm->fromArray([
            'channel' => $channel,
            'data'    => [
                'conversation_id' => $conversation['id'],
                'message_id'      => $chatMessage['id'],
                'author_id'       => $chatMessage['author_id'],
                'author_name'     => $chatMessage['author_name'],
                'message'         => $chatMessage['content'],
                'date_created'    => $chatMessage['date_created']->getTimestamp(),
            ],
            'created_by_client' => $byClientId,
            'for_person'        => $agent,
        ]);

        return [$newChatCm];
    }

    public static function createChatEndedMessages($byClientId, ChatConversation $conversation)
    {
        $channel = 'chat.chat-ended';

        $chatCm = new ClientMessage();
        $chatCm->fromArray([
            'channel' => $channel,
            'data'    => [
                'conversation_id' => $conversation['id'],
                'date_created'    => time(),
            ],
            'created_by_client' => $byClientId,
        ]);

        return [$chatCm];
    }

    public static function createChatAssignedMessages($byClientId, ChatConversation $conversation)
    {
        $clientMessages = [];

        $chatCm = new ClientMessage();

        $chatMessage = $conversation->messages->get(0);

        // We only need to notify the one guy
        if ($conversation['agent']) {
            $chatCm->fromArray([
                'channel' => 'chat.new-chat-assigned',
                'data'    => [
                    'conversation_id' => $conversation['id'],
                    'message_id'      => $chatMessage['id'],
                    'author_id'       => $chatMessage['author_id'],
                    'author_name'     => $chatMessage['author_name'],
                    'message'         => $chatMessage['content'],
                    'date_created'    => $chatMessage['date_created']->getTimestamp(),
                ],
                'created_by_client' => $byClientId,
                'for_person'        => $conversation['agent'],
            ]);

            // Dispatch a 'new chat' type popup for everyone
        } else {
            $chatCm = new ClientMessage();
            $chatCm->fromArray([
                'channel' => 'chat.new-chat',
                'data'    => [
                    'conversation_id' => $conversation['id'],
                    'message_id'      => $chatMessage['id'],
                    'author_id'       => $chatMessage['author_id'],
                    'author_name'     => $chatMessage['author_name'],
                    'message'         => $chatMessage['content'],
                    'date_created'    => $chatMessage['date_created']->getTimestamp(),
                ],
                'created_by_client' => $byClientId,
            ]);
        }

        $clientMessages[] = $chatCm;

        // Dispatch a general message, so the interfaces that are beeping can
        // can hide the beep
        $chatCm = new ClientMessage();
        $chatCm->fromArray([
            'channel' => 'chat_user_agent.chat-assigned',
            'data'    => [
                'conversation_id' => $conversation['id'],
                'agent_id'        => $conversation['agent'] ? $conversation['agent']['id'] : 0,
            ],
            'created_by_client' => $byClientId,
        ]);

        $clientMessages[] = $chatCm;

        // User should be notiifed too
        if (!$conversation['is_agent'] and $conversation->session) {
            $chatCmUser = new ClientMessage();
            $chatCmUser->fromArray([
                'channel' => 'chat_user.chat-assigned',
                'data'    => [
                    'conversation_id' => $conversation['id'],
                    'agent_id'        => $conversation['agent'] ? $conversation['agent']['id'] : 0,
                ],
                'created_by_client' => $byClientId,
                'for_client'        => $conversation->session['id'],
            ]);

            $clientMessages[] = $chatCmUser;
        }

        return $clientMessages;
    }

    public static function createPartisipatedUpdatedMessages($byClientId, ChatConversation $conversation)
    {
        $cmData = [
            'conversation_id' => $conversation->getId(),
            'agent_id'        => $conversation['agent'] ? $conversation['agent']['id'] : 0,
            'participant_ids' => [],
        ];

        foreach ($conversation->participants as $part) {
            $cmData['participant_ids'][] = $part['id'];
        }

        $channel = 'chat_user_agent.chat-parts-updated';

        $cms = [];

        // Assigned agent
        if ($conversation->agent) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $conversation->agent,
            ]);

            $cms[] = $cm;
        }

        // Participants first
        foreach ($conversation->participants as $part) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $part,
            ]);

            $cms[] = $cm;
        }

        return $cms;
    }

    public static function createNewChatRoundRobinMessages($byClientId, ChatConversation $conversation, ChatMessage $chatMessage)
    {
        $newChatCm = new ClientMessage();
        $newChatCm->fromArray([
            'channel' => 'chat.new-chat-assigned',
            'data'    => [
                'conversation_id' => $conversation['id'],
                'message_id'      => $chatMessage['id'],
                'author_id'       => $chatMessage['author_id'],
                'author_name'     => $chatMessage['author_name'],
                'message'         => $chatMessage['content'],
                'date_created'    => $chatMessage['date_created']->getTimestamp(),
            ],
            'created_by_client' => $byClientId,
            'for_person'        => $conversation['agent'],
        ]);

        return [$newChatCm];
    }

    public static function createNewMessageMessages($byClientId, ChatMessage $chatMessage, &$cmData = null)
    {
        $conversation = $chatMessage->conversation;

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

        $cms = [];

        // Assigned agent
        if ($conversation->agent) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $conversation->agent,
            ]);

            $cms[] = $cm;
        }

        // Participants first
        foreach ($conversation->participants as $part) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $part,
            ]);

            $cms[] = $cm;
        }

        // And the user
        if (!$conversation['is_agent'] and !$chatMessage['is_user_hidden']) {
            $session = $conversation->session;

            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_client'        => $session['id'],
            ]);

            $cms[] = $cm;
        }

        return $cms;
    }

    public static function createUserTypingMessages($byClientId, ChatConversation $conversation, $partialMessage)
    {
        $cmData = [
            'conversation_id' => $conversation['id'],
            'partial_message' => $partialMessage,
        ];

        $channel = 'chat.user-typing';

        $cms = [];

        // Assigned agent
        if ($conversation->agent) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $conversation->agent,
            ]);

            $cms[] = $cm;
        }

        // Participants first
        foreach ($conversation->participants as $part) {
            $cm = new ClientMessage();
            $cm->fromArray([
                'channel'           => $channel,
                'data'              => $cmData,
                'created_by_client' => $byClientId,
                'for_person'        => $part,
            ]);

            $cms[] = $cm;
        }

        return $cms;
    }
}
