<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\HttpFoundation\Cookie;

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
class ChatController extends AbstractController
{
	/**
	 * This is like DeskPRO:ClientMessages except that it's exclusively for chat,
	 * and the channels are hard-coded for chat. The chat client
	 * doesnt need to maintain a list of subscriptions.
	 *
	 * @param  $session_code
	 */
	public function pollAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$session = $chat_manager->getSession();
		$convo = $chat_manager->getChat();

		if (!$convo) {
			// It might've been closed, but we still want the events to tell about it being closed!
			if ($this->in->getUint('conversation_id')) {
				$convo = $this->em->find('DeskPRO:ChatConversation', $this->in->getUint('conversation_id'));
				if (!$convo || !$convo->session || $convo->session->id != $session->id) {
					$convo = null;
				}
			}

			// Nothing to do if we have no convo
			if (!$convo) {
				return $this->createJsonResponse(array());
			}
		}

		if ($convo) {
			$this->container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));
		}

		// Not uint because -1 will be used when no messages have ever existed
		$since = $this->in->getInt('since');

		// if $since is 0, the client is new and asking for us to send it the last id
		if ($since == 0) {
			$data = array('messages' => array(), 'last_id' => -1);
			$last_id = $this->db->fetchColumn("SELECT id FROM client_messages ORDER BY id DESC LIMIT 1");
			if ($last_id) {
				$data['last_id'] = $last_id;
			}

		} else {
			$channels = array();
			$channels[] = $convo->getChannelId();

			$data = array();
			if ($since) {
				$data = array('messages' => array(), 'last_id' => -1);

				$all_messages = $this->em->getRepository('DeskPRO:ClientMessage')->getMessagesForClientInChannels($session['id'], 0, $channels, $since);
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
			}
		}

		if ($data['last_id'] == -1) {
			unset($data['last_id']);
		}

		if ($convo) {
			$data['conversation_id'] = $convo->id;
		}

		return $this->createJsonResponse($data);
	}


	/**
	 * Handles a user sending a new message
	 *
	 * @param  $session_code
	 */
	public function sendMessageAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$convo = $chat_manager->getChat();

		if (!$convo) {
			$convo = $chat_manager->startChat($_REQUEST);
		} elseif ($this->in->getString('content')) {
			$chat_manager->addUserMessage($convo, $this->in->getString('content'));
		}

		$this->container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));

		$response = $this->createJsonResponse(array(
			'conversation_id' => $convo['id'],
		));
		$response->setLastModified(date_create('-1 day'));
		$response->setExpires(date_create("-1 day"));
		return $response;
	}

	/**
	 * @param $conversation_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function sendFileAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$convo = $chat_manager->getChat();

		$blob = $this->em->getRepository('DeskPRO:Blob')->find($this->in->getUint('send_blob_id'));

		$msg = "File: <a href=\"{$blob->getDownloadUrl(true)}\" target=\"_blank\">" . htmlspecialchars($blob->filename) . "</a> (" . $blob->getReadableFilesize() . ")";
		if ($blob->isImage()) {
			$msg .= '<div class="file-thumb"><img src="' . $blob->getThumbnailUrl(50, true) . '" /></div>';
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$sessionObj = $this->get('session');
		$session = $sessionObj->getEntity();
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $session));
		$msg = $chat_manager->addMessage(
			$convo,
			$sessionObj->getPerson(),
			$msg,
			array('is_html' => true, 'type' => 'file', 'blob_id' => $blob->id)
		);

		$this->container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));

		return $this->createJsonResponse($msg->getInfo());
	}


	/**
	 * Sends client messages to show typing indicator
	 *
	 * @param  $session_code
	 */
	public function userTypingAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$convo = $chat_manager->getChat();

		if (!$convo) {
			return $this->createJsonResponse(array());
		}

		$chat_manager->setUserTypingIndicator($convo, $this->in->getString('partial_message'));
		$this->container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));

		return $this->createJsonResponse(array());
	}


	/**
	 * This inits a session, and sets the various cookies. Then
	 * calls the dpchat (from the view) to set it on the client.
	 */
	public function chatSessionAction()
	{
		// First lets see if anyone is even available for chatting
		if (!$this->em->getRepository('DeskPRO:Session')->hasAvailableAgents()) {
			$response = $this->render('UserBundle:Chat:chat-session-unavailable.js.php');
			$response->setLastModified(date_create('-1 day'));
			$response->setExpires(date_create("-1 day"));
			$response->headers->set('Content-Type', 'text/javascript');
			return $response;
		}

		// Inits the session which isn't usually created on this controller
		// Then the session creates a new sess and visitor, and sets those
		// cookies
		$sessionObj = $this->get('session');
		$session = $sessionObj->getEntity();

		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $session));
		$convo = $chat_manager->getChat();

		// If the user is on a new page, tell the agent
		if ($convo) {
			$chat_manager->addUserTrack($convo, $session->getVisitor()->getLastPage());
			$this->container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));
		}

		$response = $this->render('UserBundle:Chat:chat-session.js.php', array(
			'session' => $session,
			'session_id' => $session->getSessionCode(),
			'conversation' => $convo,
		));

		$response->setLastModified(date_create('-1 day'));
		$response->setExpires(date_create("-1 day"));
		$response->headers->set('Content-Type', 'text/javascript');
		return $response;
	}

	/**
	 * When a chat ends, or the user ends the chat, they get to enter their name/email
	 * for a transcript.
	 */
	public function chatEndedAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$convo = $chat_manager->getChat();
		$session = $chat_manager->getSession();

		if (!$convo) {
			if ($this->in->getUint('conversation_id')) {
				$convo = $this->em->find('DeskPRO:ChatConversation', $this->in->getUint('conversation_id'));
				if (!$convo || !$convo->session || $convo->session->id != $session->id) {
					$convo = null;
				}
			}
		}

		if (!$convo) {
			return $this->createResponse('');
		}

		$sent_transcript = false;
		if ($convo['status'] != ChatConversation::STATUS_ENDED) {
			$chat_manager->endChatUser($convo);

			// The transcript is sent automatically by the chat manager,
			// set this flag so the JS knows though
			$sent_transcript = (($convo->person && $convo->person->getPrimaryEmailAddress()) || $convo->person_email);
		}

		return $this->createJsonResponse(array('ended' => true, 'sent_transcript' => $sent_transcript));
	}

	protected function _sendTranscript($convo, $email, $name)
	{
		$convo_messages = $this->em->createQuery("
			SELECT m
			FROM DeskPRO:ChatMessage m
			WHERE m.conversation = ?1 AND m.is_user_hidden = false
			ORDER BY m.id DESC
		")->setParameter(1, $convo)->execute();

		$vars = array(
			'convo' => $convo,
			'convo_messages' => $convo_messages
		);

		$email_subject = 'Chat Transcript';
		$email_body = $this->container->get('templating')->render('DeskPRO:emails_user:chat-transcript.html.twig', $vars);

		$message = $this->container->getMailer()->createMessage();
		$message->setTo($email, $name);
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		$this->container->getMailer()->send($message);
	}

	public function chatEndedFeedbackAction($session_code)
	{
		$chat_manager = $this->getChatManager($session_code);
		$convo = $chat_manager->getChat();
		$session = $chat_manager->getSession();

		if (!$convo) {
			if ($this->in->getUint('conversation_id')) {
				$convo = $this->em->find('DeskPRO:ChatConversation', $this->in->getUint('conversation_id'));
				if (!$convo || !$convo->session || $convo->session->id != $session->id) {
					$convo = null;
				}
			}
		}

		if (!$convo) {
			return $this->createJsonResponse(array('success' => false));
		}

		if (!$convo->person_email && $this->in->getString('email') && \Orb\Validator\StringEmail::isValueValid($this->in->getString('email'))) {
			$convo->person_email = $this->in->getString('email');

			$this->_sendTranscript($convo, $convo->person_email, '');
		}

		if (!$this->in->getBool('no_feedback')) {
			if ($this->in->getString('comments')) {
				$convo->rating_comment = $this->in->getString('comments');
			}
			if ($this->in->getUint('rating_response_time')) {
				$convo->rating_response_time = $this->in->getInt('rating_response_time');
			}
			if ($this->in->getUint('rating_overall')) {
				$convo->rating_overall = $this->in->getInt('rating_overall');
			}
		}

		$this->em->persist($convo);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}


	/**
	 * @param $session_code
	 * @return \Application\DeskPRO\Chat\UserChat\UserChatManager
	 */
	public function getChatManager($session_code)
	{
		$session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($session_code);
		if (!$session) {
			return null;
		}

		return $this->container->getSystemObject('user_chat_manager', array('session' => $session));
	}
}
