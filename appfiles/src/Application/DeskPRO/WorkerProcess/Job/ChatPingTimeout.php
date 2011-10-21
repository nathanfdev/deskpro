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
		#------------------------------
		# Agent timeouts
		#------------------------------

		$cutoff = date('Y-m-d H:m:s', time() - 20); // 20 secs for agents
		$chat_ids = App::getDb()->fetchAllCol("
			SELECT c.id
			FROM chat_conversations c
			LEFT JOIN sessions AS s ON s.person_id = c.agent_id
			WHERE c.agent_id IS NOT NULL
			AND c.date_last < $cutoff
			ORDER BY s.id DESC
		");

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = App::getSystemObject('user_chat_manager', array('session' => null));

		while ($chat_id = array_pop($chat_ids)) {
			$chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
			$chat_manager->agentTimeout($chat);
		}

		#------------------------------
		# User timeouts
		#------------------------------

		$cutoff = date('Y-m-d H:m:s', time() - 60); // 60 for users
		$chat_ids = App::getDb()->fetchAllCol("
			SELECT c.id
			FROM chat_conversations c
			LEFT JOIN sessions AS s ON s.id = c.session_id
			WHERE c.status = 'open'
			AND c.date_last < $cutoff
			ORDER BY s.id DESC
		");

		while ($chat_id = array_pop($chat_ids)) {
			$chat = App::getEntityRepository('DeskPRO:ChatConversation')->find($chat_id);
			$chat_manager->userTimeout($chat);
		}
	}
}
