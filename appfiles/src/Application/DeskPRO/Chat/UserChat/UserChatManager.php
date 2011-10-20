<?php

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;
use Orb\Util\Util;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Translate\Translate;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Visitor;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;

use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;
use Application\DeskPRO\Chat\StatusCheck as ChatStatusCheck;

/**
 * Manages interactions between users, agents and the server.
 */
class UserChatManager
{
	/**
	 * @var \Doctrine\ORM\Doctrine\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\Translate\Translate
	 */
	protected $tr;

	/**
	 * @var \Application\DeskPRO\Entity\Session
	 */
	protected $session;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 */
	protected $visitor;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Chat\UserChat\AutoAssigner
	 */
	protected $auto_assigner;

	public function __construct(Session $session = null, EntityManager $em, Translate $translate)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
		$this->tr = $translate;

		$this->session     = $session;
		$this->visitor     = $session->getVisitor();
		$this->person      = $session->getPerson();
	}


	/**
	 * Set the auto-assigner
	 *
	 * @param $assigner
	 * @return void
	 */
	public function setAutoAssigner(AutoAssigner $assigner)
	{
		$this->auto_assigner = $assigner;
	}


	/**
	 * Start a new chat conversation, or if its within time and sitll open, resume the previous.
	 *
	 * @return void
	 */
	public function startChat(array $chat_options, $is_window_mode = false)
	{
		$convo = $this->em->getRepository('DeskPRO:ChatConversation')->getLatestChatForSession($this->session);

		$is_new_convo = false;
		if (!$convo) {
			$convo = new ChatConversation();
			$convo->session = $this->session;
			$convo->visitor = $this->visitor;
			if ($this->person) {
				$convo->person = $this->person;
			}

			if (isset($chat_options['department_id'])) {
				$dep = $this->em->getRepository('DeskPRO:Department')->find($chat_options['department_id']);
				if ($convo->department) {
					$convo->department = $dep;
				}
			}
			if (isset($chat_options['name'])) {
				$convo->person_name = $chat_options['name'];
			}
			if (isset($chat_options['email'])) {
				$convo->person_email = $chat_options['email'];
			}
			$is_new_convo = true;
		}

		if ($is_window_mode) {
			$convo['is_window'] = true;
		}

		$this->em->beginTransaction();

		try {

			$this->em->persist($convo);
			$this->em->flush();

			if (!$convo->agent && $this->auto_assigner) {
				$assign_agent = $this->auto_assigner->getAgent($convo);
				if ($assign_agent) {
					$this->assignAgent($convo, $assign_agent);
				}
			}

			$newchat_cm_data = $convo->getInfo();

			if (isset($chat_options['content']) && $chat_options['content']) {
				$this->addUserMessage($convo, $chat_options['content']);
				$newchat_cm_data['initial_message'] = $chat_options['content'];
			}

			if ($is_new_convo) {
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'chat.new',
					'data' => $newchat_cm_data,
					'created_by_client' => $this->session->getId(),
				));

				$this->em->persist($cm);
				$this->em->flush();
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $convo;
	}


	/**
	 * Get an open chat for the users session
	 *
	 * @return \Application\DeskPRO\Entity\ChatConversation
	 */
	public function getChat()
	{
		$convo = $this->em->getRepository('DeskPRO:ChatConversation')->getLatestChatForSession($this->session);
		return $convo;
	}


	/**
	 * Assigns a chat to an agent
	 *
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @param $agent
	 * @return void
	 */
	public function assignAgent(ChatConversation $convo, Person $agent)
	{
		if (!$agent->is_agent) {
			throw new \InvalidArgumentException("Person `{$agent->id}` is not an agent");
		}

		$old_agent_id = $convo->agent_id;

		$this->em->beginTransaction();
		try {
			$convo->agent = $agent;
			$this->em->persist($convo);

			$this->addSystemMessage($convo, 'user.chat.assigned_to', array('name' => $agent->display_name), array('chat_assigned' => true));

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => 'chat.reassigned',
				'data' => array_merge($convo->getInfo(), array('old_agent_id' => $old_agent_id)),
				'created_by_client' => $this->session->getId(),
			));

			$this->em->persist($cm);

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}


	/**
	 * Unassign the chat
	 *
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @param $agent
	 * @return void
	 */
	public function unassignAgent(ChatConversation $convo)
	{
		$old_agent_id = $convo->getAgentId();

		$this->em->beginTransaction();
		try {
			$convo->agent = null;
			$this->em->persist($convo);

			$this->addSystemMessage($convo, 'user.chat.unassigned', array(), array('chat_unassigned' => true));
			$this->em->persist($cm);

			// Try to reassign
			if ($this->auto_assigner) {
				$assign_agent = $this->auto_assigner->getAgent($convo);
				if ($assign_agent) {
					$this->assignAgent($convo, $assign_agent);
				}
			}

			// If no agent auto-assigned,
			// need to broadcast an alert to other agents
			if (!$convo->agent) {
				$cm = new ClientMessage();
				$cm->fromArray(array(
					'channel' => 'chat.unassigned',
					'data' => array_merge($convo->getInfo(), array('old_agent_id' => $old_agent_id)),
					'created_by_client' => $this->session->getId(),
				));
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}


	/**
	 * @param $reason
	 * @return void
	 */
	public function endChat(ChatConversation $convo, Person $author, $reason = '')
	{
		$convo->status = 'ended';

		$this->em->beginTransaction();

		try {
			$this->em->persist($convo);

			if ($author) {
				$this->addSystemMessage($convo, 'ended_by', array('name' => $author->getDisplayName()), array('chat_ended'));
			} else {
				$this->addSystemMessage($convo, 'ended', array(), array('chat_ended'));
			}

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => 'chat.ended',
				'data' => $convo->getInfo(),
				'created_by_client' => $this->session->getId(),
			));

			$this->em->persist($cm);

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}


	/**
	 * The user ended the chat
	 *
	 * @throws \Exception
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @return void
	 */
	public function endChatUser(ChatConversation $convo)
	{
		$convo->status = 'ended';

		$this->em->beginTransaction();

		try {
			$this->em->persist($convo);

			$this->addSystemMessage($convo, 'ended_user', array(), array('chat_ended'));

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => 'chat.ended',
				'data' => $convo->getInfo(),
				'created_by_client' => $this->session->getId(),
			));

			$this->em->persist($cm);

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}
	}


	/**
	 * Add a new message form the user who started the chat.
	 *
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @param $message
	 * @param array $metadata
	 * @return \Application\DeskPRO\Entity\ChatMessage
	 */
	public function addUserMessage(ChatConversation $convo, $message, array $metadata = array())
	{
		$person = $this->person;
		if (!$person || !$this->person->id) {
			$person = null;
		}

		return $this->addMessage($convo, $person, $message, $metadata);
	}


	/**
	 * Add a new message from a user
	 *
	 * @param \Application\DeskPRO\Entity\Person $author
	 * @param $message
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @return \Application\DeskPRO\Entity\ChatMessage
	 */
	public function addMessage(ChatConversation $convo, Person $author = null, $message, array $metadata = array())
	{
		$msg = new ChatMessage();
		if ($author) {
			$msg->author = $author;
		}
		$msg->content = $message;

		if (isset($metadata['user_hidden'])) {
			$msg->is_user_hidden = true;
			unset($metadata['user_hidden']);
		}

		$convo->addMessage($msg);

		$this->em->beginTransaction();

		try {
			$this->em->persist($msg);
			$this->em->persist($convo);

			$channel = $convo->getChannelId('newmessage');
			if ($msg->is_user_hidden) {
				$channel = $convo->getChannelId('newmessage_hidden');
			}

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $msg->getInfo(),
				'created_by_client' => $this->session->getId()
			));

			$this->em->persist($cm);

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $msg;
	}


	/**
	 * @param $message_id
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @return void
	 */
	public function addSystemMessage(ChatConversation $convo, $message_id, array $vars = array(), $metadata = array())
	{
		$message = $this->tr->phrase('user.chat.' . $message_id, $vars);

		$msg = new ChatMessage();
		$msg->is_sys = true;
		$msg->content = $message;

		if (isset($metadata['user_hidden'])) {
			$msg->is_user_hidden = true;
			unset($metadata['user_hidden']);
		}

		$msg->metadata = $metadata;

		$convo->addMessage($msg);

		$this->em->beginTransaction();

		try {
			$this->em->persist($msg);
			$this->em->persist($convo);

			$channel = $convo->getChannelId('newmessage');
			if ($msg->is_user_hidden) {
				$channel = $convo->getChannelId('newmessage_hidden');
			}

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $channel,
				'data' => $msg->getInfo(),
				'created_by_client' => $this->session->getId()
			));

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $msg;
	}


	/**
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @param $preview_string
	 * @return void
	 */
	public function setUserTypingIndicator(ChatConversation $convo, $preview_string)
	{
		$this->em->beginTransaction();

		try {
			$this->em->persist($msg);
			$this->em->persist($convo);

			$cm = new ClientMessage();
			$cm->fromArray(array(
				'channel' => $convo->getChannelId('usertyping'),
				'data' => array('preview' => $preview_string),
				'created_by_client' => $this->session->getId()
			));

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $msg;
	}


	public function getSession()
	{
		return $this->session;
	}
}
