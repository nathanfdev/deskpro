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

/**
 * Handles ticket searches
 */
class ChatController extends AbstractController
{
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
	}

	
	/**
	 * Sending an agent message is less formal in that we automatically
	 * create conversations based on time, instead of having
	 * chats created first.
	 *
	 * @param  $agent_id
	 */
	public function sendAgentMessageAction($agent_id)
	{
		$date_cut = new \DateTime('-5 hours');
		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getRecentForPeople(array($agent_id, $this->person['id']), $date_cut);

		if (!$conversation) {
			$conversation = new ChatConversation();
			$conversation['is_agent'] = true;
			$conversation->addParticipant($agent_id);
			$conversation->addParticipant($this->person);
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($conversation);
		App::getOrm()->flush();
		$res = $this->sendMessageAction($conversation);
		App::getOrm()->commit();

		return $res;
	}

	
	public function getOnlineAgentsAction()
	{
		$cutoff = time() - App::getSetting('core.sessions_lifetime');

		$online_agents = array();

		$sessions = App::getOrm()->createQuery("
			SELECT s,p
			FROM DeskPRO:Session s
			LEFT JOIN s.person p
			WHERE p.is_agent = true AND s.date_last > ?1
			GROUP BY p.id
			ORDER BY s.id DESC
		")->setParameter(1, $cutoff)->execute();

		foreach ($sessions as $sess) {
			$online_agents[] = array(
				'agent_id'   => $sess->person['id'],
				'agent_name' => $sess->person['display_name'],
			);
		}

		return $this->createJsonResponse(array('online_agents' => $online_agents));
	}

	############################################################################
	# List old chats
	############################################################################

	/**
	 * List the articles
	 */
	public function agentHistoryAction()
	{
		$agent_chats = App::getEntityRepository('DeskPRO:ChatConversation')->getAgentList();

		$is_partial = false;
		$tpl = 'AgentBundle:AgentChat:list.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:AgentChat:list-part.html.twig';
		}

		return $this->render($tpl, array(
			'agent_chats' => $agent_chats,
		));
	}
}