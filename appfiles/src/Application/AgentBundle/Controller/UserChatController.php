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

use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;

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

		$quick_replies = App::getEntityRepository('DeskPRO:ChatQuickReply')->getRepliesForPerson($this->person);

		// Needed for agent assign menu
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$agent_names = $ticket_options['agents'];

		return $this->render('AgentBundle:UserChat:view.html.twig', array(
			'convo_messages' => $convo_messages,
			'quick_replies' => $quick_replies,
			'convo' => $conversation,
			'agent_names' => $agent_names
		));
	}

	
	/**
	 * [JSON] Get a QR
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function getQuickReplyAction($conversation_id, $quick_reply_id)
	{
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		$qr = App::findEntity('DeskPRO:ChatQuickReply', $quick_reply_id);

		$reply = $qr->getReplyForConversation($conversation);

		return $this->createJsonResponse(array(
			'qr_id' => $qr['id'],
			'reply' => $reply,
		));
	}


	/**
	 * Reassign a chat
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function assignChatAction($conversation_id, $agent_id)
	{
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		$agent = App::findEntity('DeskPRO:Person', $agent_id);
		if (!$agent) {
			$agent = null;
		}
		$conversation->setAgent($agent);

		$client_messages = ChatClientMessageGenerator::createChatAssignedMessages(
			App::getSession()->getEntityId(),
			$conversation
		);

		App::getOrm()->transactional(function ($em) use ($conversation, $client_messages) {
			$em->persist($conversation);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return $this->createJsonResponse(array(
			
		));
	}


	/**
	 * End a chat
	 *
	 * @param  $conversation_id
	 */
	public function endChatAction($conversation_id)
	{
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		$conversation['status'] = 'ended';
		$client_messages = ChatClientMessageGenerator::createChatEndedMessages(
			App::getSession()->getEntityId(),
			$conversation
		);

		App::getOrm()->transactional(function ($em) use ($conversation, $client_messages) {
			$em->persist($conversation);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return $this->createJsonResponse(array(

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

		$chat_message = $conversation->addNewMessage(
			$this->in->getString('content'),
			$this->person
		);

		$client_messages = ChatClientMessageGenerator::createNewMessageMessages(
			App::getSession()->getEntityId(),
			$chat_message
		);

		App::getOrm()->transactional(function ($em) use ($conversation, $client_messages) {
			$em->persist($conversation);

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
	 * List the articles
	 */
	public function getSectionDataAction()
	{
		$agent_names = App::getEntityRepository('DeskPRO:Person')->getAgentNames();

		$html = $this->renderView('AgentBundle:UserChat:window-section.html.twig', array(
			'agent_names' => $agent_names,
		));

		return $this->createJsonResponse(array('section_html' => $html));
	}
}