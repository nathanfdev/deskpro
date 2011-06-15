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

use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;
use Application\DeskPRO\Chat\StatusCheck as ChatStatusCheck;

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
	 * This is like DeskPRO:ClientMessages except that it's exclusively for chat,
	 * and the channels are hard-coded for chat. The chat client
	 * doesnt need to maintain a list of subscriptions.
	 * 
	 * @param  $session_code
	 */
	public function pollAction($session_code)
	{
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($session_code);
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

			if (mt_rand(1,10) <= 5) {
				$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session);
				if ($conversation) {
					$status_check = new ChatStatusCheck($conversation, $session);
					$status_check->runChecks();
				}
			}

			$channels = array(
				'chat.message',
				'chat.chat-ended',
				'chat.proactive',
			);

			$data = array();
			if ($since) {
				$data = array('messages' => array(), 'last_id' => -1);

				$all_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessagesForClientInChannels($session['id'], $person_id, $channels, $since);
				foreach ($all_messages as $message) {
					$handler = $message->getHandler();

					if ($message['created_by_client'] != $session['id']) {
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

		return $this->createJsonpResponse($data);
	}


	/**
	 * Handles a user sending a new message
	 * 
	 * @param  $session_code
	 */
	public function sendMessageAction($session_code)
	{
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($session_code);
		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session);

		App::getOrm()->beginTransaction();

		$is_new_convo = false;
		if (!$conversation) {
			$conversation = ChatConversation::newForUserSession($session);

			$dep_id = $this->in->getUint('department_id');
			if ($dep_id) {
				$dep = App::findEntity('DeskPRO:Department', $dep_id);
				$conversation->department = $dep;
			}

			if ($this->in->getBool('is_window')) {
				$conversation['is_window'] = true;
			}
			$is_new_convo = true;
		}

		$chat_message = $conversation->addNewMessageForSession(
			$this->in->getString('content'),
			$session
		);

		App::getOrm()->persist($conversation);
		App::getOrm()->flush();

		$client_messages = array();

		if ($is_new_convo) {

			if (App::getSetting('core_chat.assign_mode') == 'round_robin') {

				$assign_agent = App::getEntityRepository('DeskPRO:Person')->getChatAgentRoundRobin();
				$conversation->agent = $assign_agent;

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewChatRoundRobinMessages(
					$session['id'],
					$conversation,
					$chat_message
				));
			} else {

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewChatMessages(
					$session['id'],
					$conversation,
					$chat_message
				));
			}
		}
		$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewMessageMessages(
			$session['id'],
			$chat_message
		));

		foreach ($client_messages as $cm) {
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
	 * Sends client messages to show typing indicator
	 *
	 * @param  $session_code
	 */
	public function userTypingAction($session_code)
	{
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($session_code);
		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session);

		if (!$conversation) {
			return $this->createJsonpResponse();
		}

		$client_messages = ChatClientMessageGenerator::createUserTypingMessages($conversation, $this->in->getString('partial_message'));
		foreach ($client_messages as $cm) {
			App::getOrm()->persist($cm);
		}

		return $this->createJsonpResponse();
	}


	/**
	 * This inits a session, and sets the various cookies. Then
	 * calls the dpchat (from the view) to set it on the client.
	 */
	public function chatSessionAction()
	{
		\Application\DeskPRO\HttpFoundation\Session::$track_from_input = true;

		// First lets see if anyone is even available for chatting
		if (!App::getEntityRepository('DeskPRO:Session')->hasAvailableAgents()) {
			return $this->render('UserBundle:Chat:chat-session-unavailable.js.php', array(

			));
		}

		// Inits the session which isn't usually created on this controller
		// Then the session creates a new sess and visitor, and sets those
		// cookies
		$sessionObj = $this->get('session');
		$session = $sessionObj->getEntity();

		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session);

		// If there exists a convo going already, but the user has popped it into
		// a separate window, then other pages will just act like chat is
		// unavailable
		if ($conversation AND $conversation['is_window']) {
			return $this->render('UserBundle:Chat:chat-session-unavailable.js.php', array(
				'conversation' => $conversation
			));
		}

		$convo_messages = false;
		if ($conversation) {
			$convo_messages = App::getOrm()->createQuery("
				SELECT m
				FROM DeskPRO:ChatMessage m
				WHERE m.conversation = ?1
				ORDER BY m.id DESC
			")->setParameter(1, $conversation)->execute();
		}

		$department_sel = null;
		if (App::getSetting('core_chat.require_department')) {
			$department_options = App::getOrm()->getRepository('DeskPRO:Department')->getFullDepartmentNames(null, false);
			$department_sel = array('<select name="department_id">');
			foreach ($department_options as $k => $v) {
				$department_sel[] = '<option value="'.$k.'">'.htmlspecialchars($v).'</option>';
			}
			$department_sel[] = '</select>';
			$department_sel = implode('', $department_sel);
		}

		$proactive_ignore_time = empty($_COOKIE['dpchat_no_proactive']) ? 0 : $_COOKIE['dpchat_no_proactive'];
		$proactive = false;
		if (!$conversation AND $proactive_ignore_time < time() - 64800) {
			if (App::getSetting('core_chat.proactive_time')) {
				$timecut = time() - App::getSetting('core_chat.proactive_time');
				if ($session['date_created']->getTimestamp() > $timecut) {
					$proactive = true;
				}
			}
			if (App::getSetting('core_chat.proactive_pages') AND $session['page_count'] > App::getSetting('core_chat.proactive_pages')) {
				$proactive = true;
			}
		}
		$proactive = true;

		return $this->render('UserBundle:Chat:chat-session.js.php', array(
			'session' => $session,
			'convo_messages' => $convo_messages,
			'conversation' => $conversation,
			'department_sel' => $department_sel,
			'proactive' => $proactive
		));
	}

	/**
	 * When a chat ends, or the user ends the chat, they get to enter their name/email
	 * for a transcript.
	 */
	public function chatEndedAction($session_code)
	{
		$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($session_code);
		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session, false);

		if ($conversation['status'] != ChatConversation::STATUS_ENDED) {
			$conversation['status'] = ChatConversation::STATUS_ENDED;

			$client_messages = ChatClientMessageGenerator::createChatEndedMessages(
				$session['id'],
				$conversation
			);

			App::getOrm()->transactional(function ($em) use ($conversation, $client_messages) {
				$em->persist($conversation);

				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}

				$em->flush();
			});
		}

		if ($this->in->getBool('process')) {

			$convo_messages = App::getOrm()->createQuery("
				SELECT m
				FROM DeskPRO:ChatMessage m
				WHERE m.conversation = ?1
				ORDER BY m.id DESC
			")->setParameter(1, $conversation)->execute();

			$vars = array(
				'convo' => $conversation,
				'convo_messages' => $convo_messages
			);

			$email_subject = 'Chat Transcript';
			$email_body = App::get('templating')->render('DeskPRO:emails_user:chat-transcript.html.twig', $vars);

			$message = App::getMailer()->createMessage();
			$message->setTo($this->in->getString('email'), $this->in->getString('name'));
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);

			return $this->render('UserBundle:Chat:chat-ended-thanks.html.twig', array(
				'session'  => $session,
				'convo'    => $conversation,
			));

		} else {
			return $this->render('UserBundle:Chat:chat-ended.html.twig', array(
				'session'  => $session,
				'convo'    => $conversation,
			));
		}
	}


	/**
	 * This inits a session, and sets the various cookies. Then
	 * calls the dpchat (from the view) to set it on the client.
	 */
	public function chatWindowAction($session_code)
	{
		// First lets see if anyone is even available for chatting
		if (!App::getEntityRepository('DeskPRO:Session')->hasAvailableAgents()) {
			//return $this->createResponse('');
		}

		$session = null;
		if ($session_code) {
			$session = App::getEntityRepository('DeskPRO:Session')->getSessionFromCode($session_code);
		}

		if (!$session) {
			$sessionObj = $this->get('session');
			$session = $sessionObj->getEntity();
		}

		$conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getLatestChatForSession($session, false);
		if ($conversation) {
			$conversation['is_window'] = true;
			App::getOrm()->transactional(function ($em) use ($conversation) {
				$em->persist($conversation);
				$em->flush();
			});
		}

		return $this->render('UserBundle:Chat:window.html.twig', array(
			'session'  => $session,
		));
	}
}