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
	const DEFAULT_INTERVAL = 60; // Every min

	public function run()
	{
		$cutoff = date('Y-m-d H:m:s', time() - App::getSetting('core.sessions_lifetime'));
		$datetime = date('Y-m-d H:i:s', time() - 60);

		// Check timeout-able agents
		App::getDb()->fetchAllCol("
			SELECT c.id
			FROM chat_conversations c
			LEFT JOIN sessions AS s ON s.person_id = c.agent_id
			WHERE c.agent_id IS NOT NULL
			AND c.date_last < $cutoff
			ORDER BY s.id DESC

			SELECT s.person_id
			FROM sessions s
			LEFT JOIN chat_conversations c ON c.agent_id = s.person_id
			WHERE c.status = 'open'
			AND s.
		");
	}
}
