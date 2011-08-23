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
class AgentChatController extends AbstractController
{
	protected $agent_chat;

	public function init()
	{
		parent::init();

		$this->agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
	}

	/**
	 * Accepts a POST of a new message to a conversation
	 */
	public function sendMessageAction($conversation_id)
	{
		$info = $this->agent_chat->sendMessage($this->in->getString('content'), $conversation_id);

		return $this->createJsonResponse(array(
			'conversation_id' => $info['conversation']['id'],
			'new_message_id'  => $info['chat_message']['id']
		));
	}


	/**
	 * Sending an agent message is less formal in that we automatically
	 * create conversations based on time, instead of having
	 * chats created first.
	 *
	 * @param string $agent_id One or more agent ID's
	 */
	public function sendAgentMessageAction($convo_id = 0)
	{
		$agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');

		$info = $this->agent_chat->sendAgentMessage($this->in->getString('content'), $agent_ids, $convo_id);

		return $this->createJsonResponse(array(
			'conversation_id' => $info['conversation']['id'],
			'new_message_id'  => $info['chat_message']['id']
		));
	}


	public function getOnlineAgentsAction()
	{
		$cutoff = date('Y-m-d H:m:s', time() - App::getSetting('core.sessions_lifetime'));

		$agent_info = array();
		$online_agents = array();

		$agents = App::getEntityRepository('DeskPRO:Person')->getAgents();

		foreach ($agents as $agent) {
			$agent_info[$agent['id']] = array(
				'agent_id'   => $agent['id'],
				'agent_name' => $agent['display_name'],
				'agent_short_name' => $agent->getDisplayContactShort(4),
				'picture_url' => $agent->getPictureUrl(10),
				'picture_url_sizable' => $agent->getPictureUrl('{SIZE}'),
			);
		}

		// TODO [UI demo]
		// - Revert real session sniffing for online agents instaed of random

		/*
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
				'agent_short_name' => $sess->person->getDisplayContactShort(4),
				'picture_url' => $sess->person->getPictureUrl(10)
			);
		}
		*/

		// get random online agent id for demo
		$rand = array_rand($agent_info);
		if ($rand == $this->person['id']) $rand = array_rand($agent_info);

		$online_agents[] = $rand;

		return $this->createJsonResponse(array(
			'agent_info'    => $agent_info,
			'online_agents' => $online_agents
		));
	}

	############################################################################
	# List old chats
	############################################################################

	/**
	 * List the articles
	 */
	public function getSectionDataAction()
	{
		$agent_chatted = App::getEntityRepository('DeskPRO:ChatConversation')->getAgentList($this->person);

		$html = $this->renderView('AgentBundle:AgentChat:window-section.html.twig', array(
			'agent_chatted' => $agent_chatted,
		));

		return $this->createJsonResponse(array('section_html' => $html));
	}

	/**
	 * List the articles
	 */
	public function agentHistoryAction($agent_id)
	{
		$agent = App::findEntity('DeskPRO:Person', $agent_id);
		$conversations = App::getEntityRepository('DeskPRO:ChatConversation')->getChatsForPeople(array(
			$this->person['id'],
			$agent['id']
		));

		$is_partial = false;
		$tpl = 'AgentBundle:AgentChat:list.html.twig';
		if ($this->in->getBool('partial')) {
			$is_partial = true;
			$tpl = 'AgentBundle:AgentChat:list-part.html.twig';
		}

		return $this->render($tpl, array(
			'agent' => $agent,
			'conversations' => $conversations,
		));
	}


	public function agentChatTranscriptAction($conversation_id)
	{
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		$convo_messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:ChatMessage m
			WHERE m.conversation = ?1
			ORDER BY m.id DESC
		")->setParameter(1, $conversation)->execute();

		return $this->render('AgentBundle:AgentChat:view.html.twig', array(
			'convo_messages' => $convo_messages,
			'convo' => $conversation,
		));
	}
}
