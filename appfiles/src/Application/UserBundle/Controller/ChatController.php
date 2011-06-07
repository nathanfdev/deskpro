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

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Handles ticket searches
 */
class ChatController extends AbstractController
{
	/**
	 * Sending an agent message is less formal in that we automatically
	 * create conversations based on time, instead of having
	 * chats created first.
	 *
	 * @param  $agent_id
	 */
	public function sendMessageAction($agent_id)
	{
		$date_cut = new \DateTime('-5 hours');
		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getActiveChatForVisitor(
			$this->person,
			$this->session->getVisitor()
		);

		if (!$conversation) {
			$conversation = new ChatConversation();
			if ($this->person['id']) {
				$conversation->person = $this->person;
			}
			$conversation->visitor = $this->session->getVisitor();
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($conversation);
		App::getOrm()->flush();
		$res = $this->sendMessageAction($conversation);
		App::getOrm()->commit();

		$chat_message = $conversation->createMessage(
			$this->in->getString('content'),
			$this->person
		);

		$client_messages = array();
		$channel = 'chat.message';
		if ($conversation['is_agent']) {
			$channel = 'agent_chat.new-message';
		}
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

		return $res;
	}
}