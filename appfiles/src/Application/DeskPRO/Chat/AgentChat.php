<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Chat
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Chat;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;

/**
 * Actions to do with AgentChat.
 * See the AgentChatController for more, there are some more that need to be decoupled.
 */
class AgentChat
{
	protected $person;
	protected $session;

	public function __construct(Person $person, Session $session)
	{
		$this->person = $person;
		$this->session = $session;
	}

	public function sendMessage($message, $conversation)
	{
		if (! ($conversation instanceof ChatConversation)) {
			$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation);
		}

		$message = htmlspecialchars($message);

		$chat_message = $conversation->addNewMessage(
			$message,
			$this->person
		);

		$client_messages = array();
		$channel = 'chat.message';
		if ($conversation['is_agent']) {
			$channel = 'agent_chat.new-message';
		}

		$part_ids = array();
		foreach ($conversation->participants as $part) {
			$part_ids[] = $part['id'];
		}

		foreach ($conversation->participants as $part) {
			if ($part['id'] == $this->person['id']) {
				continue;
			}

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => array(
					'conversation_id'   => $conversation['id'],
					'participant_ids'   => $part_ids,
					'message_id'        => $chat_message['id'],
					'author_id'         => $chat_message->author['id'],
					'message'           => $chat_message['content'],
					'date_created'      => $chat_message['date_created']->getTimestamp()
				),
				'created_by_client' => $this->session['id'],
				'for_person' => $part
			));

			$client_messages[] = $cm;
		}

		App::getOrm()->transactional(function ($em) use ($conversation, $client_messages) {
			$em->persist($conversation);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return array(
			'conversation' => $conversation,
			'new_message'  => $chat_message
		);
	}

	public function sendAgentMessage($message, array $agent_ids, $convo_id = 0)
	{
		$em = App::getOrm();

		$conversation = null;
		if ($convo_id) {
			$conversation = $em->find('DeskPRO:ChatConversation', $convo_id);
			if ($conversation AND !$conversation->hasParticipant($this->person)) {
				// invalid convo if we're not part of it
				// sneaky hobitses
				$conversation = null;
			}
		}

		// Try to find an existing convo
		if (!$conversation) {
			$date_cut = new \DateTime('-5 hours');

			$find_agent_ids = $agent_ids;
			$find_agent_ids[] = $this->person['id'];

			$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getRecentForPeople($find_agent_ids, $date_cut);
		}

		if (!$conversation) {
			$conversation = new ChatConversation();
			$conversation['is_agent'] = true;
			$conversation->addParticipant($this->person);
			foreach ($agent_ids as $aid) {
				$conversation->addParticipant($aid);
			}
		}

		$em->beginTransaction();
		$em->persist($conversation);
		$em->flush();
		$res = $this->sendMessage($message, $conversation);
		$em->commit();

		return $res;
	}
}
