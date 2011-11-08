<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * This cycles through chats and cleans up abandonded ones
 */
class ChatPingTimeout extends AbstractJob
{
	const DEFAULT_INTERVAL = 30; // 30 secs (though cron prolly only possible to do 1 min)

	public function run()
	{
		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = App::getSystemObject('user_chat_manager', array('session' => null));

		#------------------------------
		# Agent timeouts
		#------------------------------

		$cutoff = date('Y-m-d H:i:s', time() - 20); // 20 secs for agents
		$timeouts = App::getDb()->fetchAllKeyValue("
			SELECT c.id, p.person_id
			FROM chat_conversations c
			LEFT JOIN chat_conversation_to_person AS p ON p.conversation_id = c.id
			LEFT JOIN sessions AS s ON s.person_id = p.person_id
			WHERE c.status = 'open' AND s.date_last < '$cutoff'
			ORDER BY s.id DESC
		");

		$count_agents = 0;
		foreach ($timeouts as $chat_id => $agent_id) {
			$chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
			$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);
			$chat_manager->agentTimeout($chat, $agent);

			$count_agents++;
			$this->logger->log("Agent {$agent->id} {$agent->display_name} timed out in chat {$chat->id}", Logger::INFO);
		}

		#------------------------------
		# User timeouts
		#------------------------------

		$cutoff = date('Y-m-d H:i:s', time()); // 60 for users
		$chat_ids = App::getDb()->fetchAllCol("
			SELECT c.id
			FROM chat_conversations c
			LEFT JOIN sessions AS s ON s.id = c.session_id
			WHERE c.status = 'open'	AND s.date_last < '$cutoff'
			ORDER BY s.id ASC
		");

		$count_users = 0;
		while ($chat_id = array_pop($chat_ids)) {
			$chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
			$chat_manager->userTimeout($chat);

			$count_users++;
			$this->logger->log("User timed out in chat {$chat->id}", Logger::INFO);
		}

		if ($count_agents || $count_users) {
			$this->logStatus("Set timeout on {$count_agents} agents and {$count_users} users in chats", array(
				'count_agents' => $count_agents,
				'count_users'  => $count_users
			));
		}
	}
}
