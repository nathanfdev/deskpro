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
use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;

/**
 * Since we dont have an actual chat server that can keep track of clients timing out etc,
 * each party of the chat basically needs to keep track of one another.
 *
 * So an agent checks for a user timeout, or the user checks for an agent timeout.
 */
class StatusCheck
{
	/**
	 * @var \Application\DeskPRO\Entity\ChatConversation
	 */
	protected $conversation;
	protected $session;
	protected $person;

	protected $is_agent = false;

	public function __construct($conversation, $session)
	{
		$this->conversation = $conversation;
		$this->session = $session;

		if ($session->person) {
			$this->person = $session->person;
			if ($this->person['is_agent']) {
				$this->is_agent = true;
			}
		}
	}

	public function runChecks()
	{
		if ($this->is_agent) {
			$this->runChecksByAgents();
		} else {
			$this->runChecksByUser();
		}
	}

	/**
	 * These checks are done by the agent:
	 * - Check if user has timedout
	 *
	 * @return void
	 */
	public function runChecksByAgents()
	{
		// Get the users session
		$user_sess = $this->conversation->session;

		$cut_close = time() - App::getSetting('core_chat.user_timeout');
		if ($user_sess) {
			$last = $user_sess['date_last']->getTimestamp();
		} else {
			$last = 0;
		}

		if ($last < $cut_close) {

			$msg = $this->conversation->addSystemMessage(
				App::getTranslator()->phrase('core_chat.msg_user_timeout'),
				true
			);

			$this->conversation->setStatus('ended');
			$client_messages = ChatClientMessageGenerator::createChatEndedMessages(
				'sys',
				$this->conversation
			);
			foreach ($this->conversation->getCreatedMessages() as $msg) {
				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewMessageMessages('sys', $msg));
			}

			foreach ($client_messages as $cm) {
				App::getOrm()->persist($cm);
			}

			App::getOrm()->persist($this->conversation);
			App::getOrm()->flush();
		}
	}
	

	/**
	 * The checks run by the user:
	 * - Check if agent has tiemdout
	 * 
	 * @return void
	 */
	public function runChecksByUser()
	{
		// Get the users session
		$user_sess = $this->conversation->session;

		$cut = time() - App::getSetting('core_chat.agent_timeout');
		$last = $user_sess['date_last']->getTimestamp();

		if ($last < $cut) {
			$msg = $this->conversation->addSystemMessage(
				App::getTranslator()->phrase('core_chat.msg_agent_timeout'),
				true
			);

			$client_messages = ChatClientMessageGenerator::createNewMessageMessages('sys', $msg);

			// And need to insert a "new chat" event for agents
			if (App::getSetting('core_chat.assign_mode') == 'round_robin') {

				$assign_agent = App::getEntityRepository('DeskPRO:Person')->getChatAgentRoundRobin();
				$conversation->agent = $assign_agent;

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewChatRoundRobinMessages(
					'sys',
					$this->conversation,
					$msg
				));
			} else {

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewChatMessages(
					'sys',
					$this->conversation,
					$msg
				));
			}

			foreach ($client_messages as $cm) {
				App::getOrm()->persist($cm);
			}

			App::getOrm()->persist($this->conversation);
			App::getOrm()->flush();
		}
	}
}