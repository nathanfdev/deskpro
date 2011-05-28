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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ArticleValidatingEdit;
use Application\DeskPRO\Entity\GlossaryWord;

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
		$conversation = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		$chat_message = $conversation->createMessage(
			$this->in->getString('message'),
			$this->person['id']
		);

		App::getOrm()->transactional(function ($em) use ($chat_message) {
			$em->persist($chat_message);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'conversation_id' => $conversation_id,
			'new_message_id'  => $chat_message['id']
		));
	}

	############################################################################
	# List old chats
	############################################################################

	/**
	 * List the articles
	 */
	public function listChatsAction()
	{
		$agent_chats = App::getEntityRepository('DeskPRO:ChatConversation')->getAgentList();

		return $this->render('AgentBundle:AgentChat:list.html.twig', array(
			'agent_chats' => $agent_chats,
		));
	}
}