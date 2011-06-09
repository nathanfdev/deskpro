<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class UserChatController extends AbstractController
{
	public function viewAction($conversation_id)
	{
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		$conversation['agent'] = $this->person;
		App::getOrm()->persist($conversation);
		App::getOrm()->flush();

		$convo_messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:ChatMessage m
			WHERE m.conversation = ?1
			ORDER BY m.id DESC
		")->setParameter(1, $conversation)->execute();

		return $this->render('AgentBundle:UserChat:view.html.twig', array(
			'convo_messages' => $convo_messages,
			'convo' => $conversation,
		));
	}

	/**
	 * Accepts a POST of a new message to a conversation
	 */
	public function sendMessageAction($conversation_id)
	{
		if ($conversation_id instanceof ChatConversation) {
			// sendAgentMessageAction calls this with the convo already
			$conversation = $conversation_id;
		} else {
			$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		}

		$chat_message = $conversation->createMessage(
			$this->in->getString('content'),
			$this->person
		);

		$client_messages = array();
		$channel = 'chat.message';
		foreach ($conversation->participants as $part) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => array(
					'conversation_id' => $conversation_id,
					'message_id'      => $chat_message['id'],
					'author_id'       => $chat_message->author['id'],
					'author_name'     => $chat_message->author['display_name'],
					'author_short_name' => $chat_message->author->getDisplayContactShort(5),
					'author_picture'  => $chat_message->author->getPictureUrl(10),
					'message'         => $chat_message['content'],
					'date_created'    => $chat_message['date_created']->getTimestamp()
				),
				'created_by_client' => App::getSession()->getEntityId(),
				'for_person' => $part
			));

			$client_messages[] = $cm;
		}

		// For the user
		$cm = new ClientMessage();
		$cm->fromArray(array(
			'channel' => $channel,
			'data' => array(
				'conversation_id' => $conversation_id,
				'message_id'      => $chat_message['id'],
				'author_id'       => $chat_message->author['id'],
				'author_name'     => $chat_message->author['display_name'],
				'author_short_name' => $chat_message->author->getDisplayContactShort(5),
				'author_picture'  => $chat_message->author->getPictureUrl(10),
				'message'         => $chat_message['content'],
				'date_created'    => $chat_message['date_created']->getTimestamp()
			),
			'created_by_client' => App::getSession()->getEntityId(),
			'for_client' => 'vis_' . $conversation['visitor']['id']
		));
		$client_messages[] = $cm;

		App::getOrm()->transactional(function ($em) use ($chat_message, $client_messages) {
			$em->persist($chat_message);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return $this->createJsonResponse(array(
			'conversation_id' => $conversation_id,
			'new_message_id'  => $chat_message['id']
		));
	}
}