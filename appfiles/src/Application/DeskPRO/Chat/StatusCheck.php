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

/**
 * Since we dont have an actual chat server that can keep track of clients timing out etc,
 * each party of the chat basically needs to keep track of one another.
 *
 * So an agent checks for a user timeout, or the user checks for an agent timeout.
 */
class StatusCheck
{
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
			$this->runAgentCheck();
		} else {
			$this->runUserCheck();
		}
	}

	public function runAgentCheck()
	{
		// Get the users session
		$user_sess = $this->conversation->session;

		$cut = time() - App::getSetting('core_chat.user_timeout');
		$last = $user_sess['date_last']->getTimestamp();

		if ($last < $cat) {
			$msg = new ChatMessage();
			$msg['content'] = 'user_timeout';
			$msg['is_sys'] = true;
			$msg['is_user_hidden'] = true;

			App::getOrm()->persist($msg);
			App::getOrm()->flush();
		}
	}

	public function runUserCheck()
	{
		// Get the users session
		$user_sess = $this->conversation->session;

		$cut = time() - App::getSetting('core_chat.agent_timeout');
		$last = $user_sess['date_last']->getTimestamp();

		if ($last < $cat) {
			$msg = new ChatMessage();
			$msg['content'] = 'agent_timeout';
			$msg['is_sys'] = true;
			$msg['is_user_hidden'] = true;

			$this->conversation['agent'] = null;

			// TODO handle reassign popups dispatch to other agents

			App::getOrm()->persist($msg);
			App::getOrm()->persist($this->conversation);
			App::getOrm()->flush();
		}
	}
}