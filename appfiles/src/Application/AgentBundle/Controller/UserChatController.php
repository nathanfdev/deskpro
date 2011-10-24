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
use Application\DeskPRO\Chat\StatusCheck as ChatStatusCheck;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class UserChatController extends AbstractController
{
	public function viewAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		$is_assigned = false;
		$is_part = false;
		if ($convo->status == 'open') {
			if (!$convo['agent']) {
				$chat_manager->assignAgent($convo, $this->person);
				$is_assigned = true;
			} elseif ($convo['agent']['id'] != $this->person['id'] AND !$convo->hasParticipant($this->person)) {
				//$is_part = true;
			}
		}

		$convo_messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:ChatMessage m
			WHERE m.conversation = ?1
			ORDER BY m.id DESC
		")->setParameter(1, $convo)->execute();

		$session = $convo->session;
		$visitor = $convo->visitor;
		$other_chats = $this->em->getRepository('DeskPRO:ChatConversation')->getPastChatsForVisitor($visitor);

		// For selector
		$agents = App::getEntityRepository('DeskPRO:Person')->getAgents();

		return $this->render('AgentBundle:UserChat:view.html.twig', array(
			'convo_messages' => $convo_messages,
			'quick_replies' => $quick_replies,
			'convo' => $convo,
			'session' => $session,
			'visitor' => $visitor,
			'other_chats' => $other_chats,
			'agents' => $agents,
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
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		$agent = App::findEntity('DeskPRO:Person', $agent_id);
		if ($agent) {
			$chat_manager->assignAgent($convo, $agent);
		} else {
			$chat_manager->unassignAgent($convo);
		}

		return $this->createJsonCmResponse();
	}

	/**
	 * Changes properties
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function changePropertiesAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		$props = $this->in->getCleanValueArray('props', 'raw', 'string');

		if (isset($props['department_id'])){
			$dep = null;
			if ($props['department_id']) {
				$dep = $this->em->find('DeskPRO:Department', $props['department_id']);
			}
			$chat_manager->setDepartment($convo, $dep, $this->person);
		}

		return $this->createJsonCmResponse();
	}


	/**
	 * Add a participant
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function addPartAction($conversation_id, $agent_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		$agent = App::findEntity('DeskPRO:Person', $agent_id);
		if (!$agent OR $convo->hasParticipant($agent)) {
			return $this->createJsonResponse(array());
		}

		$convo->addParticipant($agent);

		$client_messages = array();
		foreach ($convo->getCreatedMessages() as $msg) {
			$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewMessageMessages(App::getSession()->getEntityId(), $msg));
		}

		$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewAddedPartMessage(
			App::getSession()->getEntityId(),
			$convo,
			$agent
		));

		$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createPartisipatedUpdatedMessages(
			App::getSession()->getEntityId(),
			$convo
		));

		App::getOrm()->transactional(function ($em) use ($convo, $client_messages) {
			$em->persist($convo);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return $this->createJsonCmResponse(array(
			'client_messages' => $client_messages
		));
	}


	/**
	 * End a chat
	 *
	 * @param  $conversation_id
	 */
	public function endChatAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		$chat_manager->endChat($convo, $this->person, '');

		return $this->createJsonCmResponse();
	}


	/**
	 * Accepts a POST of a new message to a conversation
	 */
	public function sendMessageAction($conversation_id)
	{
		if ($conversation_id instanceof ChatConversation) {
			// sendAgentMessageAction calls this with the convo already
			$convo = $conversation_id;
		} else {
			$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
		$chat_manager->addMessage($convo, $this->person, $this->in->getString('content'));

		return $this->createJsonCmResponse();
	}


	public function sendFileAction($conversation_id)
	{
		if ($conversation_id instanceof ChatConversation) {
			// sendAgentMessageAction calls this with the convo already
			$convo = $conversation_id;
		} else {
			$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		}

		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($this->in->getUint('send_blob_id'));

		$msg = "File: <a href=\"{$blob->getDownloadUrl(true)}\" target=\"_blank\">" . htmlspecialchars($blob->filename) . "</a> (" . $blob->getReadableFilesize() . ")";
		if ($blob->isImage()) {
			$msg .= '<div class="file-thumb"><img src="' . $blob->getThumbnailUrl(50, true) . '" /></div>';
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
		$chat_manager->addMessage(
			$convo,
			$this->person,
			$msg,
			array('is_html' => true, 'type' => 'file', 'blob_id' => $blob->id)
		);

		return $this->createJsonCmResponse();
	}


	/**
	 * List the articles
	 */
	public function getSectionDataAction()
	{
		$agent_names = App::getEntityRepository('DeskPRO:Person')->getAgentNames();

		// Initial counts
		$initial_counts = App::getDb()->fetchAllKeyValue("
			SELECT IF(agent_id, agent_id, 0) AS agent_id, COUNT(*) AS count
			FROM chat_conversations c
			WHERE c.status = 'open'
			GROUP BY agent_id
		");

		$html = $this->renderView('AgentBundle:UserChat:window-section.html.twig', array(
			'counts' => $initial_counts,
			'agent_names' => $agent_names,
		));

		return $this->createJsonResponse(array('section_html' => $html));
	}


	public function listChatsAction($agent_id)
	{
		$agent = null;
		if ($agent_id) {
			$agent = App::findEntity('DeskPRO:Person', $agent_id);
		}
		$convos = App::getEntityRepository('DeskPRO:ChatConversation')->getConversationsForAgent($agent);

		return $this->render('AgentBundle:UserChat:open-list.html.twig', array(
			'agent' => $agent,
			'convos' => $convos
		));
	}


	/**
	 * Creates a JSON response but with client messages as well
	 *
	 * @param array $other_data
	 * @return \Application\DeskPRO\HttpKernel\Controller\Response
	 */
	protected function createJsonCmResponse(array $other_data = array())
	{
		$client_messages = false;
		if ($this->in->getUint('client_messages_since')) {
			$client_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		$other_data['client_messages'] = $client_messages;

		return $this->createJsonResponse($other_data);
	}
}
