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
class ChatController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	/**
	 * Input reader
	 * @var \Orb\Input\Reader\Reader
	 */
	public $in;
	
	public function init()
	{
		$this->in = $this->get('deskpro.core.input_reader');
	}


	/**
	 * This is like DeskPRO:ClientMessages except that it's exclusively for chat, and the client code
	 * is the visitor id, and the channels are hard-coded for chat. The chat client
	 * doesnt need to maintain a list of subscriptions.
	 * 
	 * @param  $visitor_code
	 */
	public function pollAction($visitor_code)
	{
		$visitor = App::getEntityRepository('DeskPRO:Visitor')->getVisitorFromCode($visitor_code);
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromVisitor($visitor);

		$person_id = ($session AND $session->person ? $session->person['id'] : null);

		// Not uint because -1 will be used when no messages have ever existed
		$since = $this->in->getInt('since');

		// if $since is 0, the client is new and asking for us to send it the last id
		if ($since == 0) {
			$data = array('messages' => array(), 'last_id' => -1);
			$last_id = App::getDb()->fetchColumn("SELECT id FROM client_messages ORDER BY id DESC LIMIT 1");
			if ($last_id) {
				$data['last_id'] = $last_id;
			}

		} else {

			$channels = array(
				'chat.message',
				'chat.proactive'
			);

			$data = array();
			if ($since) {
				$data = array('messages' => array(), 'last_id' => -1);

				$all_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessagesForClient("vis_" . $visitor, $person_id, $channels, $since);
				foreach ($all_messages as $message) {
					$handler = $message->getHandler();

					if ($message['created_by_client'] != $this->session->getEntityId()) {
						$data['messages'][] = array(
							$message['channel'],
							$handler->getMessage('ajax')
						);
					}

					if ($message['id'] > $data['last_id']) {
						$data['last_id'] = $message['id'];
					}
				}

				if ($data['last_id'] == -1) {
					unset($data['last_id']);
				}
			}
		}

		return $this->createJsonResponse($data);
	}


	/**
	 * Handles a user sending a new message
	 * 
	 * @param  $visitor_code
	 */
	public function sendMessageAction($visitor_code)
	{
		$visitor = App::getEntityRepository('DeskPRO:Visitor')->getVisitorFromCode($visitor_code);
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromVisitor($visitor);

		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getActiveChatForVisitor($visitor);

		$is_new_convo = false;
		if (!$conversation) {
			$conversation = new ChatConversation();
			if ($session AND $session->person['id']) {
				$conversation->person = $session->person;
			}
			$conversation->visitor = $visitor;
			$is_new_convo = true;
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($conversation);
		App::getOrm()->flush();

		$chat_message = $conversation->createMessage(
			$this->in->getString('content'),
			null
		);

		App::getOrm()->persist($chat_message);

		if ($is_new_convo) {
			$new_chat_cm = new ClientMessage();
			$new_chat_cm->fromArray(array(
				'channel' => 'chat.new-chat',
				'data' => array(
					'conversation_id'   => $conversation['id'],
					'message_id'        => $chat_message['id'],
					'author_id'         => $chat_message['author_id'],
					'author_name'       => $chat_message['author_name'],
					'message'           => $chat_message['content'],
					'date_created'      => $chat_message['date_created']->getTimestamp()
				),
				'created_by_client' => "vis_" . $visitor['id']
			));

			App::getOrm()->persist($new_chat_cm);
		}

		$client_messages = array();
		$channel = 'chat.message';
		$parts = $conversation->participants->toArray();
		if ($conversation->agent) {
			$parts[] = $conversation->agent;
		}
		foreach ($parts as $part) {
			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => array(
					'conversation_id'   => $conversation['id'],
					'message_id'        => $chat_message['id'],
					'author_id'         => $chat_message['author_id'],
					'author_name'       => $chat_message['author_name'],
					'message'           => $chat_message['content'],
					'date_created'      => $chat_message['date_created']->getTimestamp()
				),
				'created_by_client' => "vis_" . $visitor['id'],
				'for_person' => $part
			));

			App::getOrm()->persist($cm);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonpResponse(array(
			'conversation_id' => $conversation['id'],
			'new_message_id'  => $chat_message['id']
		));
	}


	/**
	 * This inits a session, which itself inits a visitor and sets the various
	 * cookies. Then
	 */
	public function chatVisitorAction()
	{
		// Inits the session which isn't usually created on this controller
		// Then the session creates a new sess and visitor, and sets those
		// cookies
		$session = $this->get('session');

		$visitor = $session->getVisitor();

		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getActiveChatForVisitor($visitor);
		$convo_messages = false;
		if ($conversation) {
			$convo_messages = App::getOrm()->createQuery("
				SELECT m
				FROM DeskPRO:ChatMessage m
				WHERE m.conversation = ?1
				ORDER BY m.id DESC
			")->setParameter(1, $conversation)->execute();
		}

		return $this->render('UserBundle:Chat:chat-visitor.js.php', array(
			'visitor' => $visitor,
			'convo_messages' => $convo_messages,
		));
	}
}