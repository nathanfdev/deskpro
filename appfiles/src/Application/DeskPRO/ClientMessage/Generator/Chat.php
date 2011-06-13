<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ClientMessage
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ClientMessage\Generator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;

class Chat
{
	public static function createNewChatMessages($by_client_id, ChatConversation $conversation, ChatMessage $chat_message)
	{
		if ($conversation['is_agent']) {
			$channel = 'agent_chat.new-chat';
		} else {
			$channel = 'chat.new-chat';
		}
		
		$new_chat_cm = new ClientMessage();
		$new_chat_cm->fromArray(array(
			'channel' => $channel,
			'data' => array(
				'conversation_id'   => $conversation['id'],
				'message_id'        => $chat_message['id'],
				'author_id'         => $chat_message['author_id'],
				'author_name'       => $chat_message['author_name'],
				'message'           => $chat_message['content'],
				'date_created'      => $chat_message['date_created']->getTimestamp()
			),
			'created_by_client' => $by_client_id
		));

		return array($new_chat_cm);
	}

	public static function createNewChatRoundRobinMessages($by_client_id, ChatConversation $conversation, ChatMessage $chat_message)
	{
		$new_chat_cm = new ClientMessage();
		$new_chat_cm->fromArray(array(
			'channel' => 'chat.new-chat-assigned',
			'data' => array(
				'conversation_id'   => $conversation['id'],
				'message_id'        => $chat_message['id'],
				'author_id'         => $chat_message['author_id'],
				'author_name'       => $chat_message['author_name'],
				'message'           => $chat_message['content'],
				'date_created'      => $chat_message['date_created']->getTimestamp()
			),
			'created_by_client' => $by_client_id,
			'for_person' => $conversation['agent']
		));

		return array($new_chat_cm);
	}

	public static function createNewMessageMessages($by_client_id, ChatMessage $chat_message)
	{
		$conversation = $chat_message->conversation;
		
		if ($conversation['is_agent']) {
			$channel = 'agent_chat.message';
		} else {
			$channel = 'chat.message';
		}

		$cm_data = array(
			'conversation_id'   => $conversation['id'],
			'message_id'        => $chat_message['id'],
			'author_id'         => $chat_message['author_id'],
			'author_name'       => $chat_message['author_name'],
			'message'           => $chat_message['content'],
			'date_created'      => $chat_message['date_created']->getTimestamp()
		);

		$cms = array();

		// Assigned agent
		if ($conversation->agent) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $cm_data,
				'created_by_client' => $by_client_id,
				'for_person' => $conversation->agent
			));

			$cms[] = $cm;
		}

		// Participants first
		foreach ($conversation->participants as $part) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $cm_data,
				'created_by_client' => $by_client_id,
				'for_person' => $part
			));

			$cms[] = $cm;
		}

		// And the user
		if (!$conversation['is_agent'] AND !$chat_message['is_user_hidden']) {

			$session = $conversation->session;
			$person = null;
			if ($session->person) {
				$person = $session->person;
			}

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $cm_data,
				'created_by_client' => $by_client_id,
				'for_person' => $person,
				'for_client' => $session['id']
			));

			$cms[] = $cm;
		}

		return $cms;
	}

	public static function createUserTypingMessages($by_client_id, ChatConversation $conversation, $partial_message)
	{
		$cm_data = array(
			'conversation_id'   => $conversation['id'],
			'partial_message' => $partial_message
		);

		$channel = 'chat.user-typing';

		$cms = array();

		// Assigned agent
		if ($conversation->agent) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $cm_data,
				'created_by_client' => $by_client_id,
				'for_person' => $conversation->agent
			));

			$cms[] = $cm;
		}

		// Participants first
		foreach ($conversation->participants as $part) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $cm_data,
				'created_by_client' => $by_client_id,
				'for_person' => $part
			));

			$cms[] = $cm;
		}

		return $cms;
	}
}